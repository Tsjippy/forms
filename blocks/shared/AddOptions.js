import { useBlockProps } from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';

export default function AddOptions({ attributes, setAttributes }) {
    const { options } = attributes;

    const addOption = () => {
        setAttributes({
            options: [...options, {value : '', label: ''}]
        });
    };

    const updateOption = (value, index, type) => {
        const newOptions = [...options];

        if(newOptions[index] == undefined){
            newOptions[index] = {value : '', label: ''};
        }

        newOptions[index][type] = value;

        setAttributes({
            options: newOptions
        });
    };

    const removeOption = (index) => {
        setAttributes({
            options: options.filter((_, i) => i !== index)
        });
    };
    return (
        <>
        {options.map((option, index) => (
            <div key={index} style={{ marginBottom: '10px' }}>
                <TextControl
                    label={`Option value ${index + 1}`}
                    value={option.value}
                    onChange={(value) => updateOption(value, index, 'value')}
                />
                <TextControl
                    label={`Option Label ${index + 1}`}
                    value={option.label}
                    onChange={(value) => updateOption(value, index, 'label')}
                />
                <Button
                    isDestructive
                    onClick={() => removeOption(index)}
                >
                    Remove Option {index + 1}
                </Button>
            </div>
        ))}

        <Button variant="primary" onClick={addOption}>
            Add Option
        </Button>
        </>
    );
}