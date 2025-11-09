import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, ColorPalette } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const {
        text,
        url,
        icon,
        backgroundColor,
        textColor,
        isPrimary
    } = attributes;

    const blockProps = useBlockProps();

    const iconOptions = [
        { label: __('Link', 'frs-partnership-portal'), value: 'link' },
        { label: __('Phone', 'frs-partnership-portal'), value: 'phone' },
        { label: __('Email', 'frs-partnership-portal'), value: 'email' },
        { label: __('Calendar', 'frs-partnership-portal'), value: 'calendar' },
        { label: __('Home', 'frs-partnership-portal'), value: 'home' },
        { label: __('Arrow Right', 'frs-partnership-portal'), value: 'arrow-right' },
        { label: __('External', 'frs-partnership-portal'), value: 'external' },
        { label: __('Download', 'frs-partnership-portal'), value: 'download' },
        { label: __('None', 'frs-partnership-portal'), value: 'none' }
    ];

    const colors = [
        { name: 'White', color: '#ffffff' },
        { name: 'Black', color: '#000000' },
        { name: 'Blue', color: '#2563eb' },
        { name: 'Green', color: '#10b981' },
        { name: 'Red', color: '#ef4444' },
        { name: 'Purple', color: '#8b5cf6' },
        { name: 'Gray', color: '#6b7280' }
    ];

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Button Settings', 'frs-partnership-portal')}>
                    <TextControl
                        label={__('Button Text', 'frs-partnership-portal')}
                        value={text}
                        onChange={(value) => setAttributes({ text: value })}
                    />
                    <TextControl
                        label={__('URL', 'frs-partnership-portal')}
                        value={url}
                        onChange={(value) => setAttributes({ url: value })}
                        help={__('Use # for anchors, tel: for phone numbers, mailto: for emails', 'frs-partnership-portal')}
                    />
                    <SelectControl
                        label={__('Icon', 'frs-partnership-portal')}
                        value={icon}
                        options={iconOptions}
                        onChange={(value) => setAttributes({ icon: value })}
                    />
                    <ToggleControl
                        label={__('Primary Button', 'frs-partnership-portal')}
                        checked={isPrimary}
                        onChange={(value) => setAttributes({ isPrimary: value })}
                        help={__('Primary buttons have enhanced styling', 'frs-partnership-portal')}
                    />
                </PanelBody>
                <PanelBody title={__('Colors', 'frs-partnership-portal')}>
                    <div style={{ marginBottom: '20px' }}>
                        <label>{__('Background Color', 'frs-partnership-portal')}</label>
                        <ColorPalette
                            colors={colors}
                            value={backgroundColor}
                            onChange={(value) => setAttributes({ backgroundColor: value })}
                        />
                    </div>
                    <div>
                        <label>{__('Text Color', 'frs-partnership-portal')}</label>
                        <ColorPalette
                            colors={colors}
                            value={textColor}
                            onChange={(value) => setAttributes({ textColor: value })}
                        />
                    </div>
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <a
                    href={url}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        padding: isPrimary ? '16px 24px' : '12px 20px',
                        backgroundColor: backgroundColor,
                        color: textColor,
                        borderRadius: '12px',
                        textDecoration: 'none',
                        fontSize: isPrimary ? '18px' : '16px',
                        fontWeight: isPrimary ? '600' : '500',
                        boxShadow: isPrimary ? '0 4px 6px rgba(0,0,0,0.1)' : '0 2px 4px rgba(0,0,0,0.05)',
                        transition: 'all 0.3s ease'
                    }}
                    onClick={(e) => e.preventDefault()}
                >
                    {icon !== 'none' && (
                        <span style={{ marginRight: '8px' }}>
                            {icon === 'phone' && '📱'}
                            {icon === 'email' && '✉️'}
                            {icon === 'calendar' && '📅'}
                            {icon === 'home' && '🏠'}
                            {icon === 'arrow-right' && '→'}
                            {icon === 'external' && '🔗'}
                            {icon === 'download' && '⬇️'}
                            {icon === 'link' && '🔗'}
                        </span>
                    )}
                    {text}
                </a>
            </div>
        </>
    );
}