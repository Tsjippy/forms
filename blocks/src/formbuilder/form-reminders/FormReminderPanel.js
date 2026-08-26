import { useEffect, useState } from '@wordpress/element';
import {
    PanelBody,
    TextControl,
    ToggleControl,
    RadioControl,
    Button,
    Notice,
    Spinner,
} from '@wordpress/components';

import WarningConditions from './WarningConditions';

import emptyReminder from './reminderDefaults';
import { getReminder, saveReminder } from './reminderApi';
import {
    getDateWindowLimits,
    hasShouldSubmitEmailTrigger,
    normaliseReminderResponse,
} from './reminderUtils';

export function FormReminderPanel({
    blockId,
    saveInMeta = false
}) {
    const [reminder, setReminder] = useState(emptyReminder);
    const [isLoading, setIsLoading] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [notice, setNotice] = useState(null);

    //const triggerFound = hasShouldSubmitEmailTrigger(emailSettings);
    const recurringEnabled = !!reminder.frequency;

    const dateWindowLimits = getDateWindowLimits(reminder);

    useEffect(() => {
        if (!blockId) {
            return;
        }

        setIsLoading(true);
        setNotice(null);

        getReminder(blockId)
            .then((response) => {
                setReminder(
                    normaliseReminderResponse(response, emptyReminder)
                );
            })
            .catch(() => {
                setNotice({
                    status: 'error',
                    message: 'Could not load form reminder settings.',
                });
            })
            .finally(() => {
                setIsLoading(false);
            });
    }, [blockId]);

    const updateReminder = (key, value) => {
        console.log(key)
        console.log(value)

        setReminder((current) => {
            console.log(current);

            let newReminder = {...current};

            newReminder[key]    = value;
            
            return newReminder;
        });

        console.log(reminder)
    };

    const handleRecurringToggle = (enabled) => {
        setReminder((current) => {
            if (!enabled) {
                return {
                    ...current,
                    frequency: '',
                    period: '',
                    window_start: '',
                    window_end: '',
                };
            }

            return {
                ...current,
                frequency: current.frequency || 1,
                period: current.period || 'days',
            };
        });
    };

    const handleSave = async () => {
        if (!blockId) {
            setNotice({
                status: 'error',
                message: 'Cannot save reminder because the block ID is missing.',
            });

            return;
        }

        setIsSaving(true);
        setNotice(null);

        try {
            await saveReminder(blockId, {
                ...reminder,
                block_id: blockId,
            });

            setNotice({
                status: 'success',
                message: 'Form reminder saved.',
            });
        } catch (error) {
            setNotice({
                status: 'error',
                message: 'Could not save form reminder.',
            });
        }

        setIsSaving(false);
    };

    const [frequency, setFrequency] = useState(
        reminder.frequency || ''
    );

    useEffect(() => {
        setFrequency(reminder.frequency || '');
    }, [reminder.frequency]);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            if (frequency !== reminder.frequency) {
                updateReminder('frequency', value)
            }
        }, 800);

        return () => clearTimeout(timeoutId);
    }, [frequency, reminder.frequency]);

    return (
        <PanelBody title="Form Reminders" initialOpen>
            {notice && (
                <Notice
                    status={notice.status}
                    isDismissible
                    onRemove={() => setNotice(null)}
                >
                    {notice.message}
                </Notice>
            )}

            {isLoading && (
                <div className="tsjippy-formbuilder-reminders__loading">
                    <Spinner />
                    <span>Loading reminder settings...</span>
                </div>
            )}

            {/* {!triggerFound && (
                <Notice status="warning" isDismissible={false}>
                    If you define form reminders you should also define an
                    e-mail with the "The form is due for submission" trigger.
                </Notice>
            )} */}

            {!saveInMeta && (
                <>
                    <ToggleControl
                        label="Enable Recurring Form Submissions"
                        checked={recurringEnabled}
                        onChange={handleRecurringToggle}
                    />

                    {recurringEnabled && (
                        <div className="tsjippy-formbuilder-reminders__recurring">
                            <h4>Recurring Submissions</h4>

                            <TextControl
                                type="number"
                                label="Request new form submissions every"
                                value={frequency}
                                min={1}
                                onChange={(value) =>
                                    setFrequency(value)
                                }
                            />

                            <RadioControl
                                label="Period"
                                selected={reminder.period}
                                options={[
                                    {
                                        label: 'Years',
                                        value: 'years',
                                    },
                                    {
                                        label: 'Months',
                                        value: 'months',
                                    },
                                    {
                                        label: 'Days',
                                        value: 'days',
                                    },
                                ]}
                                onChange={(value) =>
                                    updateReminder('period', value)
                                }
                            />

                            <h4>Date Window</h4>

                            <p>
                                Allow submissions within this date window.
                            </p>

                            <TextControl
                                type="date"
                                label="From"
                                value={reminder.window_start || ''}
                                min={dateWindowLimits.min}
                                max={dateWindowLimits.max}
                                onChange={(value) =>
                                    updateReminder('window_start', value)
                                }
                            />

                            <TextControl
                                type="date"
                                label="To"
                                value={reminder.window_end || ''}
                                min={dateWindowLimits.min}
                                max={dateWindowLimits.max}
                                onChange={(value) =>
                                    updateReminder('window_end', value)
                                }
                            />
                        </div>
                    )}
                </>
            )}

            <div className="tsjippy-formbuilder-reminders__amount">
                <h4>Reminder Amount</h4>

                <p>
                    How many times should people be reminded? Leave empty for
                    unlimited.
                </p>

                <RadioControl
                    label="Remind once every"
                    selected={reminder.reminder_period}
                    options={[
                        {
                            label: 'Week',
                            value: 'week',
                        },
                        {
                            label: 'Day',
                            value: 'day',
                        },
                    ]}
                    onChange={(value) =>
                        updateReminder('reminder_period', value)
                    }
                />

                <TextControl
                    type="number"
                    label="For this many times"
                    value={reminder.reminder_amount || ''}
                    min={0}
                    onChange={(value) =>
                        updateReminder('reminder_amount', value)
                    }
                />
            </div>

            <div className="tsjippy-formbuilder-reminders__start-date">
                <h4>Start reminding from</h4>

                <TextControl
                    type="date"
                    label="Reminder start date"
                    value={reminder.reminder_start_date || ''}
                    min={dateWindowLimits.min}
                    max={dateWindowLimits.max}
                    onChange={(value) =>
                        updateReminder('reminder_start_date', value)
                    }
                />
            </div>

            <div className="tsjippy-formbuilder-reminders__conditions">
                <h4>Warning Exclusions</h4>

                <WarningConditions
                    value={reminder.conditions || []}
                    onChange={(conditions) =>
                        updateReminder('conditions', conditions)
                    }
                />
            </div>

            <Button
                variant="primary"
                onClick={handleSave}
                disabled={isSaving || isLoading || !blockId}
            >
                {isSaving ? 'Saving...' : 'Save form reminder'}
            </Button>
        </PanelBody>
    );
}