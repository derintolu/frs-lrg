// Debug: Check if WordPress block functions are available
console.log('FRS Prequal Heading Block Loading...', {
    registerBlockType: typeof registerBlockType,
    wp: typeof wp
});

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

registerBlockType('lrh/prequal-heading', {
    edit: function Edit({ attributes, setAttributes, context }) {
        const blockProps = useBlockProps({
            className: 'frs-prequal-heading-editor'
        });

        // Get the current post ID
        const postId = useSelect(select => select('core/editor').getCurrentPostId());
        const postType = useSelect(select => select('core/editor').getCurrentPostType());

        // Use meta fields directly
        const [meta, setMeta] = useEntityProp('postType', postType, 'meta', postId);

        // Get values from meta
        const line1 = meta._frs_prequal_heading_line1 || attributes.line1;
        const line2 = meta._frs_prequal_heading_line2 || attributes.line2;

        // Update meta when changed
        const updateLine1 = (value) => {
            setMeta({
                ...meta,
                _frs_prequal_heading_line1: value
            });
            setAttributes({ line1: value });
        };

        const updateLine2 = (value) => {
            setMeta({
                ...meta,
                _frs_prequal_heading_line2: value
            });
            setAttributes({ line2: value });
        };

        return (
            <div {...blockProps}>
                {/* Beautiful Hero Preview Card */}
                <div style={{
                    background: 'linear-gradient(135deg, #1a365d 0%, #2b77c9 100%)',
                    borderRadius: '12px',
                    padding: '40px 30px',
                    boxShadow: '0 10px 40px rgba(0,0,0,0.2)',
                    position: 'relative',
                    overflow: 'hidden'
                }}>
                    {/* Background Pattern */}
                    <div style={{
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        opacity: 0.1,
                        background: 'url("data:image/svg+xml,%3Csvg width="60" height="60" xmlns="http://www.w3.org/2000/svg"%3E%3Cdefs%3E%3Cpattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"%3E%3Cpath d="M 60 0 L 0 0 0 60" fill="none" stroke="white" stroke-width="1"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width="100%25" height="100%25" fill="url(%23grid)"/%3E%3C/svg%3E")',
                        pointerEvents: 'none'
                    }} />
                    
                    {/* Label */}
                    <div style={{
                        display: 'inline-block',
                        background: 'rgba(255,255,255,0.2)',
                        backdropFilter: 'blur(10px)',
                        padding: '4px 12px',
                        borderRadius: '20px',
                        marginBottom: '25px'
                    }}>
                        <span style={{
                            color: 'white',
                            fontSize: '11px',
                            fontWeight: '600',
                            textTransform: 'uppercase',
                            letterSpacing: '1px'
                        }}>
                            Hero Section Heading
                        </span>
                    </div>

                    {/* Main Content */}
                    <div style={{
                        textAlign: 'center',
                        position: 'relative',
                        zIndex: 1
                    }}>
                        <RichText
                            tagName="h1"
                            value={line1}
                            onChange={updateLine1}
                            placeholder="Enter first line..."
                            style={{
                                fontSize: '42px',
                                fontWeight: '800',
                                color: '#00d4ff',
                                marginBottom: '5px',
                                lineHeight: '1.2',
                                textShadow: '0 2px 20px rgba(0,212,255,0.3)',
                                background: 'linear-gradient(135deg, #00d4ff 0%, #0099ff 100%)',
                                WebkitBackgroundClip: 'text',
                                WebkitTextFillColor: 'transparent',
                                backgroundClip: 'text'
                            }}
                        />
                        <RichText
                            tagName="h2"
                            value={line2}
                            onChange={updateLine2}
                            placeholder="Enter second line..."
                            style={{
                                fontSize: '36px',
                                fontWeight: '700',
                                color: 'white',
                                lineHeight: '1.2',
                                textShadow: '0 2px 10px rgba(0,0,0,0.2)'
                            }}
                        />
                    </div>

                    {/* Mini Team Preview */}
                    <div style={{
                        marginTop: '30px',
                        display: 'flex',
                        justifyContent: 'center',
                        gap: '20px'
                    }}>
                        <div style={{
                            width: '60px',
                            height: '60px',
                            borderRadius: '50%',
                            background: 'linear-gradient(135deg, #0a6ff9, #00a1ff)',
                            border: '3px solid white',
                            boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: 'white',
                            fontSize: '20px',
                            fontWeight: 'bold'
                        }}>
                            R
                        </div>
                        <div style={{
                            width: '60px',
                            height: '60px',
                            borderRadius: '50%',
                            background: 'linear-gradient(135deg, #00a1ff, #0a6ff9)',
                            border: '3px solid white',
                            boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: 'white',
                            fontSize: '20px',
                            fontWeight: 'bold'
                        }}>
                            L
                        </div>
                    </div>

                    {/* Decorative Elements */}
                    <div style={{
                        position: 'absolute',
                        top: '-50px',
                        right: '-50px',
                        width: '150px',
                        height: '150px',
                        borderRadius: '50%',
                        background: 'rgba(255,255,255,0.05)',
                        pointerEvents: 'none'
                    }} />
                    <div style={{
                        position: 'absolute',
                        bottom: '-30px',
                        left: '-30px',
                        width: '100px',
                        height: '100px',
                        borderRadius: '50%',
                        background: 'rgba(0,212,255,0.1)',
                        pointerEvents: 'none'
                    }} />
                </div>

                {/* Helper Text */}
                <p style={{ 
                    color: '#666',
                    fontSize: '13px',
                    marginTop: '15px',
                    textAlign: 'center',
                    fontStyle: 'italic'
                }}>
                    💡 This heading appears in the hero section with the team avatars
                </p>
            </div>
        );
    },

    save: () => {
        // Return null since we're using PHP render
        return null;
    }
});