import apiFetch from '@wordpress/api-fetch';
import { Button, Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

import EmailEditor from './EmailEditor';
import EmailTriggerPanel from './EmailTriggerPanel';
import EmailAddressPanel from './EmailAddressPanel';

export function EmailSettings({
	blockId = false,
	formBlocks = [],
}) {
	const [emails, setEmails] = useState([
		{
			trigger: {
				type: 'submitted',
				block: '',
				operator: '==',
				compare: '',
				conditionalField: '',
				conditionalValue: '',
				conditionalFields: [],
				daysBefore: 0,
				daysAfter: 0,
			},
			sender: {
				type: 'fixed',
				email: '',
				rules: [],
				elseEmail: '',
			},
			recipient: {
				type: 'fixed',
				email: '%email%',
				rules: [],
				elseEmail: '',
			},
			subject: '',
			message: '',
			headers: '',
			attachments: '',
		}
	]);
	const [activeTab, setActiveTab] = useState(0);
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);

	useEffect(() => {
		if (!blockId) {
			return;
		}

		setLoading(true);

        apiFetch({
			path: `${tsjippy.restApiPrefix}/forms/get_form_emails`,
			method: 'POST',
			data: {
				blockId: blockId
			},
		})
			.then((response) => {
				setEmails(response || []);
			})
			.catch((error) => {
				console.error(error);
			})
			.finally(() => {
				setLoading(false);
			});
	}, [blockId]);

	const saveEmails = () => {
		setSaving(true);

		apiFetch({
			path: `${tsjippy.restApiPrefix}/forms/save_form_emails`,
			method: 'POST',
			data: {
				blockId: blockId,
				emails,
			},
		})
			.catch((error) => {
				console.error(error);
			})
			.finally(() => {
				setSaving(false);
			});
	};

	const updateEmail = (index, changes) => {
		const updated = [...emails];

		updated[index] = {
			...updated[index],
			...changes,
		};

		setEmails(updated);
	};

	const addEmail = () => {
		const updated = [
			...emails,
			{
				trigger: {
					type: 'submitted',
					block: '',
					operator: '==',
					compare: '',
					conditionalField: '',
					conditionalValue: '',
					conditionalFields: [],
					daysBefore: 0,
					daysAfter: 0,
				},
				sender: {
					type: 'fixed',
					email: '',
					rules: [],
					elseEmail: '',
				},
				recipient: {
					type: 'fixed',
					email: '%email%',
					rules: [],
					elseEmail: '',
				},
				subject: '',
				message: '',
				headers: '',
				attachments: '',
			},
		];

		setEmails(updated);
		setActiveTab(updated.length - 1);
	};

	const removeEmail = (index) => {
		const updated = emails.filter(
			(_, i) => i !== index
		);

		setEmails(updated);

		if (activeTab >= updated.length) {
			setActiveTab(
				Math.max(0, updated.length - 1)
			);
		}
	};

	if (loading) {
		return <Spinner />;
	}

	const email = emails[activeTab];

	return (
		<div className="tsjippy-email-settings">
			<div
				className="tsjippy-email-tabs"
				style={{
					display: 'flex',
					gap: '8px',
					marginBottom: '20px',
				}}
			>
				{emails.map((item, index) => (
					<Button
						key={index}
						variant={
							activeTab === index
								? 'primary'
								: 'secondary'
						}
						onClick={() =>
							setActiveTab(index)
						}
					>
						E-mail {index + 1}
					</Button>
				))}

				<Button
					variant="secondary"
					onClick={addEmail}
				>
					+
				</Button>
			</div>

			{email && (
				<>
					<EmailTriggerPanel
						value={email.trigger}
						formBlocks={formBlocks}
						onChange={(trigger) =>
							updateEmail(activeTab, {
								trigger,
							})
						}
					/>

					<EmailAddressPanel
						title="Sender Address"
						value={email.sender}
						formBlocks={formBlocks}
						onChange={(sender) =>
							updateEmail(activeTab, {
								sender,
							})
						}
					/>

					<EmailAddressPanel
						title="Recipient Address"
						value={email.recipient}
						formBlocks={formBlocks}
						onChange={(recipient) =>
							updateEmail(activeTab, {
								recipient,
							})
						}
					/>

					<EmailEditor
						email={email}
						formBlocks={formBlocks}
						onChange={(changes) =>
							updateEmail(
								activeTab,
								changes
							)
						}
					/>

					<Button
						isDestructive
						onClick={() =>
							removeEmail(activeTab)
						}
					>
						Remove E-mail
					</Button>
				</>
			)}

			<div style={{ marginTop: '20px' }}>
				<Button
					variant="primary"
					isBusy={saving}
					onClick={saveEmails}
				>
					Save Email Configuration
				</Button>
			</div>
		</div>
	);
}