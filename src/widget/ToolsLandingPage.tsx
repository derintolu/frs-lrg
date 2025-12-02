import React, { useState } from 'react';
import { MortgageCalculatorWidget } from './MortgageCalculatorWidget';
import { PropertyValuation } from '../frontend/portal/components/loan-officer-portal/PropertyValuation';

interface ToolsLandingPageProps {
  loanOfficerId?: number;
  loanOfficerName?: string;
  loanOfficerEmail?: string;
  loanOfficerPhone?: string;
  loanOfficerNmls?: string;
  loanOfficerTitle?: string;
  loanOfficerAvatar?: string;
  webhookUrl?: string;
  showLeadForm?: boolean;
}

type ToolType = 'mortgage-calculator' | 'property-valuation';

export function ToolsLandingPage(props: ToolsLandingPageProps) {
  const [activeTool, setActiveTool] = useState<ToolType>('mortgage-calculator');
  const [showEmbedCode, setShowEmbedCode] = useState(false);

  // Separate color controls
  const [gradientStart, setGradientStart] = useState('#2563eb'); // Blue
  const [gradientEnd, setGradientEnd] = useState('#2dd4da'); // Cyan
  const [headshot, setHeadshot] = useState<string>(props.loanOfficerAvatar || '');

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
    return `<!-- Mortgage Calculator Widget -->
<div id="mortgage-calculator"
     data-loan-officer-id="${props.loanOfficerId || ''}"
     data-loan-officer-name="${props.loanOfficerName || ''}"
     data-loan-officer-email="${props.loanOfficerEmail || ''}"
     data-loan-officer-phone="${props.loanOfficerPhone || ''}"
     data-loan-officer-nmls="${props.loanOfficerNmls || ''}"
     data-loan-officer-title="${props.loanOfficerTitle || ''}"
     data-loan-officer-avatar="${headshot}"
     data-gradient-start="${gradientStart}"
     data-gradient-end="${gradientEnd}"
     data-show-lead-form="${props.showLeadForm ? 'true' : 'false'}"
     ${props.webhookUrl ? `data-webhook-url="${props.webhookUrl}"` : ''}
></div>
<script src="${baseUrl}/wp-content/plugins/frs-lrg/assets/widget/widget.js"></script>
<link rel="stylesheet" href="${baseUrl}/wp-content/plugins/frs-lrg/assets/widget/widget.css">`;
  };

  const copyEmbedCode = () => {
    navigator.clipboard.writeText(generateEmbedCode());
    alert('Embed code copied to clipboard!');
  };

  return (
    <div className="tools-landing-page min-h-screen bg-gradient-to-br from-slate-50 to-white">
      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8 text-center">
          <h1 className="text-4xl font-bold text-gray-900 mb-2">Professional Tools</h1>
          <p className="text-gray-600">Mortgage Calculator & Property Valuation</p>
        </div>

        {/* Tool Switcher */}
        <div className="flex justify-center mb-8">
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
                <div className="flex gap-4">
                  <div className="flex-1">
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
                  <div className="flex-1">
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
              <MortgageCalculatorWidget
                config={{
                  loanOfficerId: props.loanOfficerId,
                  loanOfficerName: props.loanOfficerName,
                  loanOfficerEmail: props.loanOfficerEmail,
                  loanOfficerPhone: props.loanOfficerPhone,
                  showLeadForm: props.showLeadForm ?? true,
                  webhookUrl: props.webhookUrl,
                  gradientStart,
                  gradientEnd
                }}
              />
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
