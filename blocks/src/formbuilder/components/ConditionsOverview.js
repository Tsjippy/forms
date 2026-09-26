import { 
  useMemo,
  useState 
} from '@wordpress/element';

import { 
  Card, 
  CardHeader, 
  CardBody, 
  Flex, 
  FlexItem, 
  TabPanel, 
  Icon,
  Button
} from '@wordpress/components';

import {  
  seen, 
  arrowRight, 
  check 
} from '@wordpress/icons';

import ConditionsModal from './ConditionsModal';

const warningIcon = (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 2L1 21h22L12 2zm0 3.5L20.1 19H3.9L12 5.5zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z" />
  </svg>
);

const flashIcon = (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
    <path d="M7 2v11h3v9l7-12h-4l4-8H7z" />
  </svg>
);

const layersIcon = (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
    <path d="M11.99 18.54l-7.37-5.73L3 14.07l9 7 9-7-1.63-1.27-7.38 5.74zM12 16l7.36-5.73L21 9l-9-7-9 7 1.63 1.27L12 16z" />
  </svg>
);

const branchIcon = (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
    <path d="M18 14a3 3 0 0 0-2.82 2H11a1 1 0 0 1-1-1V8.82A3.001 3.001 0 0 0 12 6a3 3 0 1 0-4 2.82V15a3 3 0 0 0 3 3h4.18A3.001 3.001 0 1 0 18 14zm-8-8a1 1 0 1 1-1 1 1 1 0 0 1 1-1zm0 12a1 1 0 1 1 1-1 1 1 0 0 1-1 1zm8 0a1 1 0 1 1 1-1 1 1 0 0 1-1 1z"/>
  </svg>
);

const controlsIcon = (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
    <path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z" />
  </svg>
);

/**
 * Normalizes input: Converts an object structure into a flat array of condition items.
 */
function normalizeConditionsData(conditionsData) {
  if (!conditionsData || typeof conditionsData !== 'object') {
    return [];
  }

  if (Array.isArray(conditionsData)) {
    return conditionsData;
  }

  return Object.entries(conditionsData).reduce((acc, [key, value]) => {
    if (Array.isArray(value)) {
      return acc.concat(value);
    }
    if (value && typeof value === 'object') {
      const item = { block_id: key, ...value };
      return acc.concat(item);
    }
    return acc;
  }, []);
}

/**
 * Dynamic analysis function designed for WordPress block conditions object schema.
 */
function analyzeBlockConditions(conditionsData, blocks) {
  const conditions = normalizeConditionsData(conditionsData);

  if (conditions.length === 0) {
    return {
      actionCounts: { setProperty: 0, visibility: 0, dynamicBounds: 0, total: 0 },
      triggers: [],
      dependencyChains: [],
      edgeCases: [],
      totalConditions: 0
    };
  }

  const actionCounts = { setProperty: 0, visibility: 0, dynamicBounds: 0, total: 0 };
  const fieldDependentsMap = new Map();
  const fieldImpactMap = new Map();
  const edgeCasesSet = new Set();

  conditions.forEach((conditionObj) => {
    // FIX: Extract targetBlockId before validating conditionObj to avoid ReferenceError
    const targetBlockId = String(conditionObj?.block_id || 'Unknown Block');
    const blockName = blocks[targetBlockId] || 'Unknown Name';

    if (!conditionObj || typeof conditionObj !== 'object') {
      edgeCasesSet.add(JSON.stringify({
        text: 'Incomplete or malformed condition payload detected on block',
        blockId: targetBlockId,
        blockName
      }));
      return;
    }

    const rulesList = Array.isArray(conditionObj.rules) ? conditionObj.rules : [];
    const actionsList = Array.isArray(conditionObj.actions) ? conditionObj.actions : [];

    // 1. Process Actions
    actionsList.forEach((act) => {
      actionCounts.total += 1;
      const actionName = String(act.action || act['action-type'] || '').toLowerCase();
      const propType = String(act.property || act['property-type'] || '').toLowerCase();

      if (['show', 'hide', 'visible', 'invisible'].includes(actionName)) {
        actionCounts.visibility += 1;
      } else if (actionName === 'set-property' || actionName === 'set_property') {
        if (['min', 'max'].includes(propType)) {
          actionCounts.dynamicBounds += 1;
        } else {
          actionCounts.setProperty += 1;
        }
      }
    });

    // 2. Process Rules
    rulesList.forEach((rule) => {
      if (blocks[rule['conditional-field']] === undefined) {
        edgeCasesSet.add(JSON.stringify({
          text: 'Invalid trigger block id on block',
          blockId: targetBlockId,
          blockName
        }));
      }

      const triggerField = String(rule['conditional-field'] || false);
      const equation = rule.equation || '';

      if (triggerField && targetBlockId) {
        if (!fieldDependentsMap.has(triggerField)) {
          fieldDependentsMap.set(triggerField, new Set());
        }
        fieldDependentsMap.get(triggerField).add(targetBlockId);
      }

      if (triggerField) {
        fieldImpactMap.set(triggerField, (fieldImpactMap.get(triggerField) || 0) + 1);
      }

      if (equation.includes('value') && blocks[rule['conditional-field-2']] == undefined) {
        edgeCasesSet.add(JSON.stringify({
          text: "Invalid comparison block id on block",
          blockId: targetBlockId,
          blockName
        }));
      }

      if (!triggerField && ['changed', 'visible', 'invisible'].includes(equation)) {
        edgeCasesSet.add(JSON.stringify({
          text: 'Dynamic state listeners ("changed", "visible", "invisible") required on block',
          blockId: targetBlockId,
          blockName
        }));
      }
    });
  });

  const dependencyChains = Array.from(fieldDependentsMap.entries()).map(([source, targets]) => ({
    source,
    targets: Array.from(targets)
  }));

  const triggers = Array.from(fieldImpactMap.entries())
    .sort((a, b) => b[1] - a[1])
    .slice(0, 4)
    .map(([id, impactCount]) => ({
      id,
      impactCount,
      level: impactCount > 3 ? 'High Impact' : 'Medium Impact'
    }));

  const edgeCases = Array.from(edgeCasesSet).map((item, i) => {
    let parsed = { text: item };
    try {
      parsed = JSON.parse(item);
    } catch (e) {}

    return {
      id: i + 1,
      title: `Detected Anomaly #${i + 1}`,
      description: parsed.text || item,
      blockId: parsed.blockId || null,
      blockName: parsed.blockName || null
    };
  });

  return {
    actionCounts,
    triggers,
    dependencyChains,
    edgeCases,
    totalConditions: conditions.length
  };
}

function parseBlocks(blocks) {
  let blockArray = {};
  if (!Array.isArray(blocks)) return blockArray;

  blocks.forEach(block => {
    if (block?.attributes?.blockId) {
      blockArray[block.attributes.blockId] = block.attributes.name ?? block.attributes.text ?? block.name;
    }
  });

  return blockArray;
}

export function ConditionsOverview({ conditions = {}, blocks = [] }) {
  const parsedBlocks = useMemo(() => parseBlocks(blocks), [blocks]);

  // FIX: Include blocks in useMemo dependencies so analysis updates if block names change
  const analysis = useMemo(() => analyzeBlockConditions(conditions, parsedBlocks), [conditions, parsedBlocks]);
  const [activeModalBlock, setActiveModalBlock] = useState(null);

  const stats = [
    { label: 'Set Property Actions', count: analysis.actionCounts.setProperty, icon: controlsIcon, color: '#2271b1' },
    { label: 'Visibility Toggles', count: analysis.actionCounts.visibility, icon: seen, color: '#8c52ff' },
    { label: 'Dynamic Bounds', count: analysis.actionCounts.dynamicBounds, icon: flashIcon, color: '#dba617' },
    { label: 'Detected Edge Cases', count: analysis.edgeCases.length, icon: warningIcon, color: '#d63638' },
  ];

  function closePopUp() {
    setActiveModalBlock(null);
  }

  return (
    <div className="wp-dynamic-conditions-wrap" style={{ maxWidth: '1100px', margin: '20px 0' }}>
      {/* FIX: Added key prop to force clean mount/unmount and passed onRequestClose */}
      {activeModalBlock && (
        <ConditionsModal
          key={activeModalBlock}
          isVisible={true}
          isOpen={true}
          onClose={closePopUp}
          onRequestClose={closePopUp}
          blockId={activeModalBlock}
          allNestedBlocks={blocks}
          blockProps={blocks.find(block => String(block?.attributes?.blockId) === String(activeModalBlock))}
        />
      )}

      <Card>
        <CardHeader style={{ padding: '16px 24px', borderBottom: '1px solid #c3c4c7' }}>
          <Flex align="center" justify="space-between">
            <FlexItem>
              <h2 style={{ margin: 0, fontSize: '18px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon icon={branchIcon} />
                Dynamic Conditional Rules Overview
              </h2>
              <p style={{ margin: '4px 0 0 0', color: '#646970', fontSize: '13px' }}>
                Real-time analysis for {analysis.totalConditions} evaluated block condition sets.
              </p>
            </FlexItem>
          </Flex>
        </CardHeader>

        <CardBody style={{ padding: '24px' }}>
          {/* Summary Stat Badges */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px', marginBottom: '24px' }}>
            {stats.map((stat, idx) => (
              <div 
                key={idx} 
                style={{ 
                  padding: '16px', 
                  backgroundColor: '#f6f7f7', 
                  borderRadius: '4px', 
                  border: '1px solid #c3c4c7',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '12px'
                }}
              >
                <div style={{ color: stat.color }}>
                  <Icon icon={stat.icon} size={28} />
                </div>
                <div>
                  <div style={{ fontSize: '22px', fontWeight: 'bold', lineHeight: 1 }}>{stat.count}</div>
                  <div style={{ fontSize: '12px', color: '#50575e', marginTop: '4px' }}>{stat.label}</div>
                </div>
              </div>
            ))}
          </div>

          <TabPanel
            className="wp-conditions-tab-panel"
            activeClass="is-active"
            tabs={[
              { name: 'rules', title: 'Overview & Stats', key: 'rules' },
              { name: 'graph', title: `Dependency Chains (${analysis.dependencyChains.length})`, key: 'graph' },
              { name: 'edge-cases', title: `Edge Cases (${analysis.edgeCases.length})`, key: 'edge-cases' },
            ]}
          >
            {(tab) => {
              if (tab.name === 'rules') {
                return (
                  <div style={{ marginTop: '20px' }}>
                    <h3 style={{ fontSize: '15px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <Icon icon={layersIcon} /> Action Patterns
                    </h3>
                    <ul style={{ listStyle: 'none', margin: 0, padding: 0, border: '1px solid #c3c4c7', borderRadius: '4px' }}>
                      <li style={{ padding: '12px 16px', borderBottom: '1px solid #f0f0f1', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div>
                          <strong>Visibility Controllers (`show` / `hide`)</strong>
                          <div style={{ fontSize: '12px', color: '#646970' }}>Toggles block visibility on condition match</div>
                        </div>
                        <span style={{ background: '#f8f0fe', color: '#8c52ff', padding: '2px 8px', borderRadius: '10px', fontSize: '12px', fontWeight: 600 }}>
                          {analysis.actionCounts.visibility} Actions
                        </span>
                      </li>
                      <li style={{ padding: '12px 16px', borderBottom: '1px solid #f0f0f1', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div>
                          <strong>Value Synchronization (`set-property`)</strong>
                          <div style={{ fontSize: '12px', color: '#646970' }}>Dynamic value resets or cross-field populates</div>
                        </div>
                        <span style={{ background: '#f0f6fc', color: '#2271b1', padding: '2px 8px', borderRadius: '10px', fontSize: '12px', fontWeight: 600 }}>
                          {analysis.actionCounts.setProperty} Actions
                        </span>
                      </li>
                      <li style={{ padding: '12px 16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div>
                          <strong>Dynamic Bounds (`min` / `max`)</strong>
                          <div style={{ fontSize: '12px', color: '#646970' }}>Dynamically constrains input limits</div>
                        </div>
                        <span style={{ background: '#fff8e5', color: '#dba617', padding: '2px 8px', borderRadius: '10px', fontSize: '12px', fontWeight: 600 }}>
                          {analysis.actionCounts.dynamicBounds} Actions
                        </span>
                      </li>
                    </ul>

                    <h3 style={{ fontSize: '15px', fontWeight: 600, margin: '24px 0 12px 0', display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <Icon icon={flashIcon} /> Top Trigger Blocks
                    </h3>
                    {analysis.triggers.length > 0 ? (
                      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '12px' }}>
                        {analysis.triggers.map((trigger) => (
                          <div key={trigger.id} style={{ padding: '12px', border: '1px solid #c3c4c7', borderRadius: '4px', background: '#f6f7f7' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '11px', fontWeight: 'bold' }}>
                              <span>FIELD {trigger.id}</span>
                              <span style={{ color: '#646970' }}>{trigger.level}</span>
                            </div>
                            <div style={{ fontSize: '12px', marginTop: '6px' }}>
                              Drives <strong>{trigger.impactCount}</strong> condition evaluations
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <p style={{ color: '#646970', fontStyle: 'italic' }}>No root trigger fields detected.</p>
                    )}
                  </div>
                );
              }

              if (tab.name === 'graph') {
                return (
                  <div style={{ marginTop: '20px' }}>
                    {analysis.dependencyChains.length > 0 ? (
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                        {analysis.dependencyChains.map((chain, index) => (
                          <div key={index} style={{ padding: '12px 16px', border: '1px solid #c3c4c7', borderRadius: '4px', background: '#f6f7f7' }}>
                            <div style={{ fontSize: '12px', color: '#50575e', marginBottom: '8px' }}>
                              Trigger Block: Block #{chain.source} {parsedBlocks[chain.source] ? `(${parsedBlocks[chain.source]})` : ''}
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }}>
                              <Icon icon={arrowRight} size={16} />
                              <span style={{ fontSize: '12px', color: '#646970' }}>Affects Blocks:</span>
                              {chain.targets.map((blockId) => (
                                <Button
                                  key={blockId}
                                  variant="link"
                                  onClick={() => setActiveModalBlock(blockId)}
                                  style={{
                                    fontSize: '12px',
                                    fontWeight: '600',
                                    color: '#2271b1',
                                    padding: '2px 8px',
                                    background: '#f0f6fc',
                                    border: '1px solid #c3c4c7',
                                    borderRadius: '3px',
                                    height: 'auto',
                                    textDecoration: 'none',
                                    cursor: 'pointer'
                                  }}
                                >
                                  #{blockId} ({parsedBlocks[blockId] ?? ''})
                                </Button>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <p style={{ color: '#646970' }}>No block dependencies found in dataset.</p>
                    )}
                  </div>
                );
              }

              if (tab.name === 'edge-cases') {
                return (
                  <div style={{ marginTop: '20px' }}>
                    {analysis.edgeCases.length > 0 ? (
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                        {analysis.edgeCases.map((item) => (
                          <div
                            key={item.id}
                            style={{
                              padding: '12px 16px',
                              borderLeft: '4px solid #dba617',
                              borderTop: '1px solid #c3c4c7',
                              borderRight: '1px solid #c3c4c7',
                              borderBottom: '1px solid #c3c4c7',
                              background: '#fff'
                            }}
                          >
                            <strong style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                              <Icon icon={warningIcon} style={{ color: '#dba617' }} /> {item.title}
                            </strong>
                            <div
                              style={{
                                margin: '6px 0 0 0',
                                fontSize: '13px',
                                color: '#50575e',
                                display: 'flex',
                                alignItems: 'center',
                                gap: '8px',
                                flexWrap: 'wrap'
                              }}
                            >
                              <span>{item.description}</span>
                              {item.blockId && (
                                <Button
                                  key={item.blockId}
                                  variant="link"
                                  onClick={() => setActiveModalBlock(item.blockId)}
                                  style={{
                                    fontSize: '12px',
                                    fontWeight: '600',
                                    color: '#2271b1',
                                    padding: '2px 8px',
                                    background: '#f0f6fc',
                                    border: '1px solid #c3c4c7',
                                    borderRadius: '3px',
                                    height: 'auto',
                                    textDecoration: 'none',
                                    cursor: 'pointer'
                                  }}
                                >
                                  #{item.blockId} ({item.blockName || parsedBlocks[item.blockId] || ''})
                                </Button>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <div
                        style={{
                          padding: '12px 16px',
                          borderLeft: '4px solid #00a32a',
                          background: '#f0fdf4',
                          color: '#00a32a',
                          display: 'flex',
                          alignItems: 'center',
                          gap: '8px'
                        }}
                      >
                        <Icon icon={check} />
                        <span>No structural anomalies or edge cases detected in the current payload.</span>
                      </div>
                    )}
                  </div>
                );
              }

              return null;
            }}
          </TabPanel>
        </CardBody>
      </Card>
    </div>
  );
}