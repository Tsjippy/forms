import { Button } from '@wordpress/components';

export default function PlaceholderPicker({
    formElements = [],
}) {
    const placeholders = [
        '%id%',
        '%subid%',
        '%formurl%',
        '%submissiondate%',
        '%editdate%',
        '%time_created%',
        '%time_last_edited%',
        '%viewhash%',
        ...formElements.map(
            (field) => `%${field.slug}%`
        ),
    ];

    const copyToClipboard = (value) => {
        navigator.clipboard.writeText(value);
    };

    return (
        <div
            style={{
                display: 'flex',
                flexWrap: 'wrap',
                gap: '4px',
                marginBottom: '10px',
            }}
        >
            {placeholders.map((token) => (
                <Button
                    key={token}
                    variant="secondary"
                    onClick={() =>
                        copyToClipboard(token)
                    }
                >
                    {token}
                </Button>
            ))}
        </div>
    );
}