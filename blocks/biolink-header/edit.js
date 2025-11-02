import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, ColorPalette, Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const {
        name,
        title,
        company,
        logoUrl,
        companyLogoUrl,
        backgroundColor,
        videoAutoplay,
        videoMuted,
        videoLoop,
        autoDetect
    } = attributes;

    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Header Settings', 'frs-partnership-portal')}>
                    <TextControl
                        label={__('Name', 'frs-partnership-portal')}
                        value={name}
                        onChange={(value) => setAttributes({ name: value })}
                        help={__('Leave empty to auto-detect from user profile', 'frs-partnership-portal')}
                    />
                    <TextControl
                        label={__('Title', 'frs-partnership-portal')}
                        value={title}
                        onChange={(value) => setAttributes({ title: value })}
                    />
                    <TextControl
                        label={__('Company', 'frs-partnership-portal')}
                        value={company}
                        onChange={(value) => setAttributes({ company: value })}
                    />
                    <ToggleControl
                        label={__('Auto-detect User Info', 'frs-partnership-portal')}
                        checked={autoDetect}
                        onChange={(value) => setAttributes({ autoDetect: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Media Settings', 'frs-partnership-portal')}>
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={(media) => setAttributes({ logoUrl: media.url })}
                            allowedTypes={['image']}
                            value={logoUrl}
                            render={({ open }) => (
                                <Button onClick={open} variant="secondary">
                                    {logoUrl ? __('Change Avatar', 'frs-partnership-portal') : __('Select Avatar', 'frs-partnership-portal')}
                                </Button>
                            )}
                        />
                    </MediaUploadCheck>
                    {logoUrl && (
                        <img src={logoUrl} alt="Avatar" style={{ width: '100px', marginTop: '10px' }} />
                    )}
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={(media) => setAttributes({ companyLogoUrl: media.url })}
                            allowedTypes={['image']}
                            value={companyLogoUrl}
                            render={({ open }) => (
                                <Button onClick={open} variant="secondary" style={{ marginTop: '10px' }}>
                                    {companyLogoUrl ? __('Change Company Logo', 'frs-partnership-portal') : __('Select Company Logo', 'frs-partnership-portal')}
                                </Button>
                            )}
                        />
                    </MediaUploadCheck>
                    {companyLogoUrl && (
                        <img src={companyLogoUrl} alt="Company Logo" style={{ width: '100px', marginTop: '10px' }} />
                    )}
                </PanelBody>
                <PanelBody title={__('Background', 'frs-partnership-portal')}>
                    <TextControl
                        label={__('Background Color/Gradient', 'frs-partnership-portal')}
                        value={backgroundColor}
                        onChange={(value) => setAttributes({ backgroundColor: value })}
                        help={__('Use CSS gradient or color value', 'frs-partnership-portal')}
                    />
                </PanelBody>
                <PanelBody title={__('Video Settings', 'frs-partnership-portal')}>
                    <ToggleControl
                        label={__('Autoplay', 'frs-partnership-portal')}
                        checked={videoAutoplay}
                        onChange={(value) => setAttributes({ videoAutoplay: value })}
                    />
                    <ToggleControl
                        label={__('Muted', 'frs-partnership-portal')}
                        checked={videoMuted}
                        onChange={(value) => setAttributes({ videoMuted: value })}
                    />
                    <ToggleControl
                        label={__('Loop', 'frs-partnership-portal')}
                        checked={videoLoop}
                        onChange={(value) => setAttributes({ videoLoop: value })}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <div style={{ background: backgroundColor, padding: '40px', textAlign: 'center', color: '#fff' }}>
                    {logoUrl && (
                        <img
                            src={logoUrl}
                            alt="Avatar"
                            style={{
                                width: '120px',
                                height: '120px',
                                borderRadius: '50%',
                                marginBottom: '20px'
                            }}
                        />
                    )}
                    <h2>{name || __('Your Name', 'frs-partnership-portal')}</h2>
                    <p>{title}</p>
                    <p>{company}</p>
                    {companyLogoUrl && (
                        <img
                            src={companyLogoUrl}
                            alt="Company Logo"
                            style={{
                                maxWidth: '200px',
                                marginTop: '20px'
                            }}
                        />
                    )}
                </div>
            </div>
        </>
    );
}