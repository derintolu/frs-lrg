// Debug: Check if WordPress block functions are available
console.log('FRS Prequal Subheading Block Loading...', {
    registerBlockType: typeof registerBlockType,
    wp: typeof wp
});

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

registerBlockType('lrh/prequal-subheading', {
    edit: function Edit({ attributes, setAttributes, context }) {
        const blockProps = useBlockProps({
            className: 'frs-prequal-subheading-editor'
        });

        // Get the current post ID
        const postId = useSelect(select => select('core/editor').getCurrentPostId());
        const postType = useSelect(select => select('core/editor').getCurrentPostType());

        // Use meta fields directly
        const [meta, setMeta] = useEntityProp('postType', postType, 'meta', postId);

        // Get value from meta
        const subheading = meta._frs_prequal_subheading || attributes.subheading;

        // Update meta when changed
        const updateSubheading = (value) => {
            setMeta({
                ...meta,
                _frs_prequal_subheading: value
            });
            setAttributes({ subheading: value });
        };

        return (
            <div {...blockProps}>
                {/* Elegant Subheading Card */}
                <div style={{
                    background: 'linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%)',
                    borderRadius: '16px',
                    padding: '30px',
                    boxShadow: '0 4px 20px rgba(0,0,0,0.08)',
                    border: '1px solid #e2e8f0',
                    position: 'relative',
                    overflow: 'hidden'
                }}>
                    {/* Subtle Background Pattern */}
                    <div style={{
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        opacity: 0.03,
                        background: 'url("data:image/svg+xml,%3Csvg width="40" height="40" xmlns="http://www.w3.org/2000/svg"%3E%3Cdefs%3E%3Cpattern id="dots" width="40" height="40" patternUnits="userSpaceOnUse"%3E%3Ccircle cx="20" cy="20" r="2" fill="currentColor"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width="100%25" height="100%25" fill="url(%23dots)"/%3E%3C/svg%3E")',
                        pointerEvents: 'none'
                    }} />
                    
                    {/* Label */}
                    <div style={{
                        display: 'inline-block',
                        background: 'rgba(99, 102, 241, 0.1)',
                        backdropFilter: 'blur(10px)',
                        padding: '4px 14px',
                        borderRadius: '20px',
                        marginBottom: '20px',
                        border: '1px solid rgba(99, 102, 241, 0.2)'
                    }}>
                        <span style={{
                            color: '#6366f1',
                            fontSize: '11px',
                            fontWeight: '600',
                            textTransform: 'uppercase',
                            letterSpacing: '1px'
                        }}>
                            Page Subheading
                        </span>
                    </div>

                    {/* Main Content */}
                    <div style={{
                        textAlign: 'center',
                        position: 'relative',
                        zIndex: 1
                    }}>
                        <RichText
                            tagName="p"
                            value={subheading}
                            onChange={updateSubheading}
                            placeholder="Enter subheading text..."
                            style={{
                                fontSize: '18px',
                                fontWeight: '500',
                                color: '#4a5568',
                                lineHeight: '1.6',
                                margin: '0',
                                textAlign: 'center'
                            }}
                        />
                    </div>

                    {/* Feature Icons */}
                    <div style={{
                        marginTop: '25px',
                        display: 'flex',
                        justifyContent: 'center',
                        gap: '15px'
                    }}>
                        <div style={{
                            width: '40px',
                            height: '40px',
                            borderRadius: '50%',
                            background: 'linear-gradient(135deg, #6366f1, #8b5cf6)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: 'white',
                            fontSize: '16px'
                        }}>
                            ⚡
                        </div>
                        <div style={{
                            width: '40px',
                            height: '40px',
                            borderRadius: '50%',
                            background: 'linear-gradient(135deg, #10b981, #059669)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: 'white',
                            fontSize: '16px'
                        }}>
                            ✓
                        </div>
                        <div style={{
                            width: '40px',
                            height: '40px',
                            borderRadius: '50%',
                            background: 'linear-gradient(135deg, #f59e0b, #d97706)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: 'white',
                            fontSize: '16px'
                        }}>
                            🏠
                        </div>
                    </div>

                    {/* Decorative Elements */}
                    <div style={{
                        position: 'absolute',
                        top: '-20px',
                        right: '-20px',
                        width: '80px',
                        height: '80px',
                        borderRadius: '50%',
                        background: 'rgba(99, 102, 241, 0.05)',
                        pointerEvents: 'none'
                    }} />
                    <div style={{
                        position: 'absolute',
                        bottom: '-15px',
                        left: '-15px',
                        width: '60px',
                        height: '60px',
                        borderRadius: '50%',
                        background: 'rgba(16, 185, 129, 0.05)',
                        pointerEvents: 'none'
                    }} />
                </div>

                {/* Helper Text */}
                <p style={{ 
                    color: '#718096',
                    fontSize: '13px',
                    marginTop: '15px',
                    textAlign: 'center',
                    fontStyle: 'italic'
                }}>
                    💡 This subheading appears below the main heading in the hero section
                </p>
            </div>
        );
    },

    save: () => {
        // Return null since we're using PHP render
        return null;
    }
});