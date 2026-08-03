export const Rendered = ({ attributes}) => {
	return (
		<div className="file-upload-wrap">
			<div className="document-preview"/>
			<div className="upload-div">
				<input type="file" className="file-upload should-edit" name={attributes.name ?? ''}/>
				<input type="hidden" className="no-reset" name="file-upload-target-dir" value={attributes.targetDir ?? ''}/>
				<input type="hidden" className="no-reset" name="fileupload[user-id]" value={attributes.userId ?? false}/>
				<input type="hidden" className="no-reset" name="fileupload[library]" value={attributes.library ?? false}/>
				<input type="hidden" className="no-reset" name="fileupload[edit]" value={attributes.edit ?? false}/>
				<input type="hidden" className="no-reset" name="fileupload[metakey]" value={attributes.metaKey ?? ''}/>
			</div>
		</div>
    );
};