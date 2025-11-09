/**
 * Open House Carousel Block
 *
 * Photo carousel for open house landing pages
 */

import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, Button, ToggleControl, RangeControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('lrh/openhouse-carousel', {
    edit: ({ attributes, setAttributes }) => {
        const { images, autoPlay, interval, address } = attributes;

        const onSelectImages = (media) => {
            const imageArray = media.map(img => ({
                id: img.id,
                url: img.url,
                alt: img.alt || '',
            }));
            setAttributes({ images: imageArray });
        };

        const removeImage = (index) => {
            const newImages = [...images];
            newImages.splice(index, 1);
            setAttributes({ images: newImages });
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Carousel Settings', 'frs-partnership-portal')}>
                        <TextControl
                            label={__('Property Address', 'frs-partnership-portal')}
                            value={address}
                            onChange={(value) => setAttributes({ address: value })}
                            placeholder="123 Main St, City, State"
                        />
                        <ToggleControl
                            label={__('Auto Play', 'frs-partnership-portal')}
                            checked={autoPlay}
                            onChange={(value) => setAttributes({ autoPlay: value })}
                        />
                        {autoPlay && (
                            <RangeControl
                                label={__('Interval (ms)', 'frs-partnership-portal')}
                                value={interval}
                                onChange={(value) => setAttributes({ interval: value })}
                                min={2000}
                                max={10000}
                                step={500}
                            />
                        )}
                    </PanelBody>
                </InspectorControls>

                <div className="openhouse-carousel-editor">
                    {address && (
                        <div className="openhouse-address-overlay">
                            <h2>{address}</h2>
                        </div>
                    )}

                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={onSelectImages}
                            allowedTypes={['image']}
                            multiple={true}
                            gallery={true}
                            value={images.map(img => img.id)}
                            render={({ open }) => (
                                <div className="openhouse-carousel-upload">
                                    {images.length === 0 ? (
                                        <Button onClick={open} variant="primary">
                                            {__('Add Property Photos', 'frs-partnership-portal')}
                                        </Button>
                                    ) : (
                                        <>
                                            <div className="openhouse-carousel-preview">
                                                {images.map((image, index) => (
                                                    <div key={image.id} className="carousel-image-item">
                                                        <img src={image.url} alt={image.alt} />
                                                        <Button
                                                            onClick={() => removeImage(index)}
                                                            isDestructive
                                                            size="small"
                                                        >
                                                            {__('Remove', 'frs-partnership-portal')}
                                                        </Button>
                                                    </div>
                                                ))}
                                            </div>
                                            <Button onClick={open} variant="secondary">
                                                {__('Add More Photos', 'frs-partnership-portal')}
                                            </Button>
                                        </>
                                    )}
                                </div>
                            )}
                        />
                    </MediaUploadCheck>
                </div>
            </>
        );
    },

    save: () => {
        // Rendered dynamically by PHP
        return null;
    },
});
