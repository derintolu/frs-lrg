import { useEffect, useState, useRef } from 'react';
import { LoadingSpinner } from '../ui/loading';

interface WordPressEditorIframeProps {
  pageId: string;
  onClose: () => void;
  onSave?: () => void;
}

export function WordPressEditorIframe({ pageId, onClose, onSave }: WordPressEditorIframeProps) {
  const [isLoading, setIsLoading] = useState(true);
  const [iframeKey, setIframeKey] = useState(0);
  const iframeRef = useRef<HTMLIFrameElement>(null);

  // Use WordPress editor in inline mode (not fullscreen)
  const editorUrl = `/wp-admin/post.php?post=${pageId}&action=edit`;

  useEffect(() => {
    // Listen for save events from the iframe
    const handleMessage = (event: MessageEvent) => {
      if (event.data?.type === 'wp-save-post' || event.data === 'wp-save-post') {
        console.log('Page saved in WordPress editor');
        if (onSave) {
          onSave();
        }
      }
    };

    window.addEventListener('message', handleMessage);

    return () => {
      window.removeEventListener('message', handleMessage);
    };
  }, [onSave]);

  const handleIframeLoad = () => {
    setIsLoading(false);

    // Inject CSS to hide WordPress admin elements and add return button
    try {
      const iframe = iframeRef.current;
      if (iframe?.contentWindow?.document) {
        const iframeDoc = iframe.contentWindow.document;

        console.log('✅ Editor loaded - PHP CSS handles styling');

        // Add close button for all users, curate controls only for authors
        setTimeout(() => {
          if (iframe.contentWindow) {
            const iframeDoc = iframe.contentWindow.document;
            const wp = iframe.contentWindow.wp;

            if (wp && wp.element && wp.components) {
              const { createElement } = wp.element;
              const { Button } = wp.components;

              // Check if user is author
              const body = iframeDoc.body;
              const isAuthor = body.classList.contains('author') || body.classList.contains('role-author');

              // Hide unnecessary editor controls for authors only
              if (isAuthor) {
                const hideControlsStyle = iframeDoc.createElement('style');
                hideControlsStyle.textContent = `
                  /* Hide all header buttons except preview and responsive controls */
                  .edit-post-header__settings > *:not(.edit-post-header-preview__button-external):not(.edit-post-header__device-preview):not([data-frs-close-button]) {
                    display: none !important;
                  }

                  /* Keep only preview and responsive device controls visible */
                  .edit-post-header-preview__button-external,
                  .edit-post-header__device-preview {
                    display: flex !important;
                  }
                `;
                iframeDoc.head.appendChild(hideControlsStyle);
                console.log('✅ Curated editor controls for author');
              }

              // Add close button for all users
              const headerSettings = iframeDoc.querySelector('.edit-post-header__settings');
              if (headerSettings) {
                const closeButtonContainer = iframeDoc.createElement('div');
                closeButtonContainer.style.cssText = 'display: flex; align-items: center; margin-right: 12px; order: -1;';
                closeButtonContainer.setAttribute('data-frs-close-button', 'true');

                headerSettings.insertBefore(closeButtonContainer, headerSettings.firstChild);

                const { render } = wp.element;
                render(
                  createElement(Button, {
                    variant: 'secondary',
                    onClick: () => {
                      window.parent.postMessage({ type: 'frs:lp:close' }, '*');
                    },
                    children: 'Close'
                  }),
                  closeButtonContainer
                );

                console.log('✅ Added close button');
              }
            }
          }
        }, 1500);
      }
    } catch (error) {
      console.error('Failed to inject CSS into iframe:', error);
    }
  };

  const handleOpenInNewTab = () => {
    window.open(editorUrl, '_blank');
  };

  const handleRefresh = () => {
    setIsLoading(true);
    setIframeKey(prev => prev + 1);
  };

  const handleSave = () => {
    try {
      // Trigger save in the iframe by dispatching a save event
      const iframe = iframeRef.current;
      if (iframe?.contentWindow) {
        iframe.contentWindow.postMessage({ type: 'trigger-save' }, '*');

        // Also try to click the save button
        const iframeDoc = iframe.contentWindow.document;
        const saveButton = iframeDoc.querySelector('.editor-post-publish-button, .editor-post-save-draft') as HTMLButtonElement;
        if (saveButton) {
          saveButton.click();
        }
      }
    } catch (error) {
      console.error('Failed to trigger save:', error);
    }
  };

  return (
    <div className="fixed bottom-0 right-0 bg-white" style={{ top: '60px', left: '320px', zIndex: 1002 }}>
      {/* Floating Close Button - Top Right */}
      <button
        onClick={onClose}
        className="fixed bg-black text-white hover:bg-gray-800 transition-colors duration-200 font-medium px-4 py-2 rounded shadow-lg"
        style={{
          top: '12px',
          right: '32px',
          zIndex: 10002
        }}
      >
        ← Back to Landing Pages
      </button>

      {/* Loading Overlay */}
      {isLoading && (
        <div className="absolute inset-0 bg-white flex items-center justify-center" style={{ zIndex: 10001 }}>
          <div className="text-center">
            <LoadingSpinner size="lg" />
            <p className="mt-4 text-gray-600">Loading Editor...</p>
          </div>
        </div>
      )}

      {/* Editor Iframe - Positioned right after sidebar (319px) and header (60px) */}
      <iframe
        ref={iframeRef}
        key={iframeKey}
        src={editorUrl}
        className="w-full h-full border-0"
        onLoad={handleIframeLoad}
        title="WordPress Block Editor"
      />
    </div>
  );
}
