import { useEffect, useState, useRef } from 'react';
import { LoadingSpinner } from '../ui/loading';
import { ArrowLeft, ExternalLink, X } from 'lucide-react';

interface WordPressEditorIframeProps {
  pageId: string;
  onClose: () => void;
  onSave?: () => void;
}

export function WordPressEditorIframe({ pageId, onClose, onSave }: WordPressEditorIframeProps) {
  const [isLoading, setIsLoading] = useState(true);
  const [iframeKey, setIframeKey] = useState(0);
  const [pageTitle, setPageTitle] = useState('Edit Page');
  const iframeRef = useRef<HTMLIFrameElement>(null);

  const editorUrl = `/wp-admin/post.php?post=${pageId}&action=edit`;

  useEffect(() => {
    // Prevent body scroll when editor is open
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = '';
    };
  }, []);

  useEffect(() => {
    const handleMessage = (event: MessageEvent) => {
      if (event.data?.type === 'wp-save-post' || event.data === 'wp-save-post') {
        console.log('Page saved in WordPress editor');
        if (onSave) {
          onSave();
        }
      }
    };

    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, [onSave]);

  const handleIframeLoad = () => {
    setIsLoading(false);

    try {
      const iframe = iframeRef.current;
      if (iframe?.contentWindow?.document) {
        const iframeDoc = iframe.contentWindow.document;

        // Get the page title
        const titleEl = iframeDoc.querySelector('.editor-post-title__input, .wp-block-post-title');
        if (titleEl) {
          setPageTitle((titleEl as HTMLElement).innerText || 'Edit Page');
        }

        setTimeout(() => {
          if (iframe.contentWindow) {
            const iframeDoc = iframe.contentWindow.document;
            const wp = (iframe.contentWindow as any).wp;

            // Check if user is author
            const body = iframeDoc.body;
            const isAuthor = body.classList.contains('author') || body.classList.contains('role-author');

            // Hide unnecessary editor controls for authors only
            if (isAuthor && wp?.element) {
              const hideControlsStyle = iframeDoc.createElement('style');
              hideControlsStyle.textContent = `
                .edit-post-header__settings > *:not(.edit-post-header-preview__button-external):not(.edit-post-header__device-preview):not([data-frs-close-button]) {
                  display: none !important;
                }
                .edit-post-header-preview__button-external,
                .edit-post-header__device-preview {
                  display: flex !important;
                }
              `;
              iframeDoc.head.appendChild(hideControlsStyle);
            }
          }
        }, 1500);
      }
    } catch (error) {
      console.error('Failed to customize iframe:', error);
    }
  };

  const handleOpenInNewTab = () => {
    window.open(editorUrl, '_blank');
  };

  return (
    <div className="fixed inset-0 z-[9999] flex flex-col bg-white">
      {/* Top Bar */}
      <div
        className="flex items-center justify-between px-6 py-3 bg-gradient-to-r from-[#0B102C] to-[#1a2040] text-white shadow-lg"
        style={{ minHeight: '56px' }}
      >
        {/* Left: Back Button */}
        <button
          onClick={onClose}
          className="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors font-medium text-sm"
        >
          <ArrowLeft className="w-4 h-4" />
          Back to Landing Pages
        </button>

        {/* Center: Title */}
        <div className="flex-1 text-center">
          <h2 className="text-lg font-semibold truncate max-w-md mx-auto">
            {pageTitle}
          </h2>
        </div>

        {/* Right: Actions */}
        <div className="flex items-center gap-3">
          <button
            onClick={handleOpenInNewTab}
            className="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm"
            title="Open in new tab"
          >
            <ExternalLink className="w-4 h-4" />
            <span className="hidden sm:inline">New Tab</span>
          </button>
          <button
            onClick={onClose}
            className="p-2 rounded-lg bg-white/10 hover:bg-red-500/80 transition-colors"
            title="Close editor"
          >
            <X className="w-5 h-5" />
          </button>
        </div>
      </div>

      {/* Iframe Container */}
      <div className="flex-1 relative">
        {/* Loading Overlay */}
        {isLoading && (
          <div className="absolute inset-0 bg-white flex items-center justify-center z-10">
            <div className="text-center">
              <LoadingSpinner size="lg" />
              <p className="mt-4 text-gray-600">Loading Editor...</p>
            </div>
          </div>
        )}

        {/* Editor Iframe - Fullscreen below the top bar */}
        <iframe
          ref={iframeRef}
          key={iframeKey}
          src={editorUrl}
          className="w-full h-full border-0"
          onLoad={handleIframeLoad}
          title="WordPress Block Editor"
        />
      </div>
    </div>
  );
}
