import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { height } = attributes;

    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Spacer Settings', 'frs-partnership-portal')}>
                    <RangeControl
                        label={__('Height (px)', 'frs-partnership-portal')}
                        value={height}
                        onChange={(value) => setAttributes({ height: value })}
                        min={0}
                        max={200}
                        step={5}
                        help={__('Adjust the vertical spacing', 'frs-partnership-portal')}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                <div
                    style={{
                        height: `${height}px`,
                        background: 'linear-gradient(45deg, #e0e0e0 25%, transparent 25%, transparent 75%, #e0e0e0 75%, #e0e0e0), linear-gradient(45deg, #e0e0e0 25%, transparent 25%, transparent 75%, #e0e0e0 75%, #e0e0e0)',
                        backgroundSize: '20px 20px',
                        backgroundPosition: '0 0, 10px 10px',
                        opacity: 0.3,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: '12px',
                        color: '#666'
                    }}
                >
                    {__('Spacer', 'frs-partnership-portal')}: {height}px
                </div>
            </div>
        </>
    );
}