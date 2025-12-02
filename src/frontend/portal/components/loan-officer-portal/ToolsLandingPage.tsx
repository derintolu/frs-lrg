import React, { useState } from 'react';
import { MortgageCalculator } from './MortgageCalculator';
import { PropertyValuation } from './PropertyValuation';

type ToolType = 'mortgage-calculator' | 'property-valuation';

export function ToolsLandingPage() {
  const [activeTool, setActiveTool] = useState<ToolType>('mortgage-calculator');
  const [showEmbedCode, setShowEmbedCode] = useState(false);

  // Separate color controls
  const [gradientStart, setGradientStart] = useState('#2563eb'); // Blue
  const [gradientEnd, setGradientEnd] = useState('#2dd4da'); // Cyan

  // Get current user info from portal config
  const currentUser = (window as any).frsPortalConfig?.currentUser || {};
  const [headshot, setHeadshot] = useState<string>(currentUser.avatar || '');

  // Handle headshot upload
  const handleHeadshotUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setHeadshot(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  // Generate embed code
  const generateEmbedCode = () => {
    const baseUrl = window.location.origin;
    const userId = (window as any).frsPortalConfig?.userId || '';

    return `<!-- Mortgage Calculator Widget -->
<div id="mortgage-calculator"
     data-loan-officer-id="${userId}"
     data-loan-officer-name="${currentUser.name || ''}"
     data-loan-officer-email="${currentUser.email || ''}"
     data-loan-officer-phone="${currentUser.phone || ''}"
     data-loan-officer-nmls="${currentUser.nmls || ''}"
     data-loan-officer-title="${currentUser.title || 'Loan Officer'}"
     data-loan-officer-avatar="${headshot}"
     data-gradient-start="${gradientStart}"
     data-gradient-end="${gradientEnd}"
     data-show-lead-form="true"
></div>
<script src="${baseUrl}/wp-content/plugins/frs-lrg/assets/widget/widget.js"></script>
<link rel="stylesheet" href="${baseUrl}/wp-content/plugins/frs-lrg/assets/widget/widget.css">`;
  };

  const copyEmbedCode = () => {
    navigator.clipboard.writeText(generateEmbedCode());
    alert('Embed code copied to clipboard!');
  };

  return (
    <div className="p-6">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Professional Tools</h1>
          <p className="text-gray-600">Mortgage Calculator & Property Valuation</p>
        </div>

        {/* Tool Switcher */}
        <div className="flex justify-start mb-8">
          <div className="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm">
            <button
              onClick={() => setActiveTool('mortgage-calculator')}
              className={`px-6 py-3 rounded-md font-semibold transition-all ${
                activeTool === 'mortgage-calculator'
                  ? 'bg-gradient-to-r from-blue-600 to-cyan-400 text-white shadow-md'
                  : 'text-gray-700 hover:bg-gray-50'
              }`}
            >
              Mortgage Calculator
            </button>
            <button
              onClick={() => setActiveTool('property-valuation')}
              className={`px-6 py-3 rounded-md font-semibold transition-all ${
                activeTool === 'property-valuation'
                  ? 'bg-gradient-to-r from-blue-600 to-cyan-400 text-white shadow-md'
                  : 'text-gray-700 hover:bg-gray-50'
              }`}
            >
              Property Valuation
            </button>
          </div>
        </div>

        {/* Mortgage Calculator Section */}
        {activeTool === 'mortgage-calculator' && (
          <div className="space-y-6">
            {/* Color Customizer */}
            <div className="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-xl font-bold text-gray-900">Customize Colors</h3>
                <button
                  onClick={() => setShowEmbedCode(!showEmbedCode)}
                  className="px-4 py-2 bg-gray-900 text-white rounded-lg font-semibold hover:bg-gray-800 transition"
                >
                  {showEmbedCode ? 'Hide' : 'Show'} Embed Code
                </button>
              </div>

              <div className="space-y-4">
                {/* Headshot Upload */}
                <div>
                  <label className="block text-sm font-semibold text-gray-700 mb-2">
                    Profile Photo
                  </label>
                  <div className="flex items-center gap-4">
                    {headshot && (
                      <img
                        src={headshot}
                        alt="Profile"
                        className="w-20 h-20 rounded-full object-cover border-2 border-gray-300"
                      />
                    )}
                    <label className="cursor-pointer bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                      {headshot ? 'Change Photo' : 'Upload Photo'}
                      <input
                        type="file"
                        accept="image/*"
                        onChange={handleHeadshotUpload}
                        className="hidden"
                      />
                    </label>
                  </div>
                </div>

                {/* Color Pickers */}
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                      Gradient Start Color
                    </label>
                    <input
                      type="color"
                      value={gradientStart}
                      onChange={(e) => setGradientStart(e.target.value)}
                      className="w-full h-12 rounded-lg border-2 border-gray-300 cursor-pointer"
                    />
                    <p className="text-xs text-gray-500 mt-1">{gradientStart}</p>
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                      Gradient End Color
                    </label>
                    <input
                      type="color"
                      value={gradientEnd}
                      onChange={(e) => setGradientEnd(e.target.value)}
                      className="w-full h-12 rounded-lg border-2 border-gray-300 cursor-pointer"
                    />
                    <p className="text-xs text-gray-500 mt-1">{gradientEnd}</p>
                  </div>
                </div>
              </div>

              {/* Embed Code Section */}
              {showEmbedCode && (
                <div className="mt-6 pt-6 border-t border-gray-200">
                  <div className="flex items-center justify-between mb-3">
                    <h4 className="font-bold text-gray-900">Embed Code</h4>
                    <button
                      onClick={copyEmbedCode}
                      className="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition text-sm"
                    >
                      Copy Code
                    </button>
                  </div>
                  <pre className="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm">
                    <code>{generateEmbedCode()}</code>
                  </pre>
                </div>
              )}
            </div>

            {/* Calculator Preview */}
            <div className="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
              <h3 className="text-xl font-bold text-gray-900 mb-4">Preview</h3>
              <div style={{
                '--gradient-start': gradientStart,
                '--gradient-end': gradientEnd,
              } as any}>
                <MortgageCalculator customGradient={{ start: gradientStart, end: gradientEnd }} />
              </div>
            </div>
          </div>
        )}

        {/* Property Valuation Section */}
        {activeTool === 'property-valuation' && (
          <div className="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <PropertyValuation />
          </div>
        )}
      </div>
    </div>
  );
}
