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
  branch, 
  layers, 
  warning, 
  controls, 
  seen, 
  flash, 
  arrowRight, 
  check 
} from '@wordpress/icons';

import ConditionsModal from './ConditionsModal';

/**
 * Normalizes input: Converts an object structure into a flat array of condition items.
 */
function normalizeConditionsData(conditionsData) {
  if (!conditionsData || typeof conditionsData !== 'object') {
    return [];
  }

  // If already an array, return it directly
  if (Array.isArray(conditionsData)) {
    return conditionsData;
  }

  // Flatten object values (handles both { id: item } and { block_id: [items] })
  return Object.entries(conditionsData).reduce((acc, [key, value]) => {
    if (Array.isArray(value)) {
      return acc.concat(value);
    }
    if (value && typeof value === 'object') {
      // Retain key as block_id if block_id isn't explicitly set inside the object
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
  const fieldDependentsMap = new Map(); // Source Field -> Set of Target Block IDs
  const fieldImpactMap = new Map();     // Source Field -> Count of Evaluations Triggered
  const edgeCasesSet = new Set();
  
  let hasInconsistentTypes = false;

  conditions.forEach((conditionObj) => {
    if (!conditionObj || typeof conditionObj !== 'object') {
      edgeCasesSet.add(` Incomplete or malformed condition payload detected. On block ${targetBlockId} (${blocks[targetBlockId]})`);
      return;
    }

    const targetBlockId = String(conditionObj.block_id || 'Unknown Block');
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

    // 2. Process Rules (Triggers and Dependencies)
    rulesList.forEach((rule) => {
      if (typeof rule['conditional-field'] === 'number') {
        hasInconsistentTypes = true;
        edgeCasesSet.add(`Inconsistent data types: "conditional-field" contains mixed string and integer keys. On block ${targetBlockId} (${blocks[targetBlockId]})`);
      }

      const triggerField = String(rule['conditional-field'] || rule.field || 'Unknown Field');
      const equation = rule.equation || '';

      // Track Source -> Target Relationship
      if (triggerField && targetBlockId) {
        if (!fieldDependentsMap.has(triggerField)) {
          fieldDependentsMap.set(triggerField, new Set());
        }
        fieldDependentsMap.get(triggerField).add(targetBlockId);
      }

      // Track Impact Frequency
      if (triggerField) {
        fieldImpactMap.set(triggerField, (fieldImpactMap.get(triggerField) || 0) + 1);
      }

      // Dynamic Operator / Listener Edge Cases
      if (equation.includes('value') && rule['conditional-field-2']) {
        edgeCasesSet.add(`Field-to-field dynamic comparison detected ('== value' operators). On block ${targetBlockId} (${blocks[targetBlockId]})`);
      }
      if (['changed', 'visible', 'invisible'].includes(equation)) {
        edgeCasesSet.add(`Dynamic state listeners ("changed", "visible", "invisible") required. On block ${targetBlockId} (${blocks[targetBlockId]})`);
      }
    });
  });

  // Build Dependency Chains
  const dependencyChains = Array.from(fieldDependentsMap.entries()).map(([source, targets]) => ({
    source,
    targets: Array.from(targets)
  }));

  // Identify Top Trigger Fields
  const triggers = Array.from(fieldImpactMap.entries())
    .sort((a, b) => b[1] - a[1])
    .slice(0, 4)
    .map(([id, impactCount]) => ({
      id,
      impactCount,
      level: impactCount > 3 ? 'High Impact' : 'Medium Impact'
    }));

  const edgeCases = Array.from(edgeCasesSet).map((msg, i) => ({
    id: i + 1,
    title: `Detected Anomaly #${i + 1}`,
    description: msg
  }));

  return {
    actionCounts,
    triggers,
    dependencyChains,
    edgeCases,
    totalConditions: conditions.length
  };
}

function parseBlocks(blocks){
    let blockArray  = {};

    blocks.forEach(block => {
        blockArray[block.attributes.blockId]    = block.attributes.name ?? block.attributes.text ?? block.name;
    });

    return blockArray;
}

export function ConditionsOverview({ conditions = {}, blocks = [] }) {
    const parsedBlocks  = parseBlocks(blocks);

  // Compute analytics dynamically when conditions object changes
  const analysis = useMemo(() => analyzeBlockConditions(conditions, parsedBlocks), [conditions]);
  const [activeModalBlock, setActiveModalBlock] = useState(null);

  const stats = [
    { label: 'Set Property Actions', count: analysis.actionCounts.setProperty, icon: controls, color: '#2271b1' },
    { label: 'Visibility Toggles', count: analysis.actionCounts.visibility, icon: seen, color: '#8c52ff' },
    { label: 'Dynamic Bounds', count: analysis.actionCounts.dynamicBounds, icon: flash, color: '#dba617' },
    { label: 'Detected Edge Cases', count: analysis.edgeCases.length, icon: warning, color: '#d63638' },
  ];

  function closePopUp(){
    setActiveModalBlock(null);
  }

  console.log(activeModalBlock)

  return (
    <div className="wp-dynamic-conditions-wrap" style={{ maxWidth: '1100px', margin: '20px 0' }}>
        {activeModalBlock && (
            <ConditionsModal
                isVisible={true}
                onClose={closePopUp}
                blockId={activeModalBlock}
                allNestedBlocks={blocks}
                blockProps={blocks.filter(block => block.attributes.blockId == activeModalBlock )[0]}
            />
        )
        }
      <Card>
        <CardHeader style={{ padding: '16px 24px', borderBottom: '1px solid #c3c4c7' }}>
          <Flex align="center" justify="space-between">
            <FlexItem>
              <h2 style={{ margin: 0, fontSize: '18px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Icon icon={branch} />
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

          {/* WordPress Core TabPanel Navigation */}
          <TabPanel
            className="wp-conditions-tab-panel"
            activeClass="is-active"
            tabs={[
              { name: 'rules', title: 'Overview & Stats', key:'rules' },
              { name: 'graph', title: `Dependency Chains (${analysis.dependencyChains.length})`, key:'graph' },
              { name: 'edge-cases', title: `Edge Cases (${analysis.edgeCases.length})`, key:'edge-cases' },
            ]}
          >
            {(tab) => {
              if (tab.name === 'rules') {
                return (
                  <div style={{ marginTop: '20px' }}>
                    <h3 style={{ fontSize: '15px', fontWeight: 600, marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <Icon icon={layers} /> Action Patterns
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

                    {/* Root Triggers */}
                    <h3 style={{ fontSize: '15px', fontWeight: 600, margin: '24px 0 12px 0', display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <Icon icon={flash} /> Top Trigger Blocks
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
                              Trigger Block:  Block #{chain.source} {parsedBlocks[chain.source] ? `(${parsedBlocks[chain.source]})` : ''}
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }}>
                              <Icon icon={arrowRight} size={16} />
                              <span style={{ fontSize: '12px', color: '#646970' }}>Affects Blocks:</span>
                              {chain.targets.map((blockId, idx) => (
                                <Button
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
                          <div key={item.id} style={{ padding: '12px 16px', borderLeft: '4px solid #dba617', borderTop: '1px solid #c3c4c7', borderRight: '1px solid #c3c4c7', borderBottom: '1px solid #c3c4c7', background: '#fff' }}>
                            <strong style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                              <Icon icon={warning} style={{ color: '#dba617' }} /> {item.title}
                            </strong>
                            <p style={{ margin: '4px 0 0 0', fontSize: '13px', color: '#50575e' }}>{item.description}</p>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <div style={{ padding: '12px 16px', borderLeft: '4px solid #00a32a', background: '#f0fdf4', color: '#00a32a', display: 'flex', alignItems: 'center', gap: '8px' }}>
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