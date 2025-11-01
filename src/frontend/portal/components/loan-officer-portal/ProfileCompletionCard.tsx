import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { Badge } from '../ui/badge';
import { Progress } from '../ui/progress';
import {
  User,
  Briefcase,
  Mail,
  FileText,
  Link as LinkIcon,
  CheckCircle,
  AlertCircle,
  ArrowRight,
} from 'lucide-react';
import {
  calculateProfileCompletion,
  getCompletionColor,
  getCompletionMessage,
  type CompletionResult,
} from '../../utils/profileCompletion';

interface ProfileCompletionCardProps {
  userData: Record<string, any>;
  onNavigate?: (view: string) => void;
  compact?: boolean;
}

const SECTION_ICONS = {
  User,
  Briefcase,
  Mail,
  FileText,
  Link: LinkIcon,
};

export function ProfileCompletionCard({
  userData,
  onNavigate,
  compact = false,
}: ProfileCompletionCardProps) {
  const completion: CompletionResult = calculateProfileCompletion(userData);
  const { percentage, completedFields, totalFields, incompleteSections } = completion;

  const isComplete = percentage === 100;
  const colorClass = getCompletionColor(percentage);

  if (compact) {
    return (
      <Card
        className="shadow-xl border-0 rounded cursor-pointer hover:shadow-2xl transition-shadow"
        onClick={() => onNavigate?.('profile')}
        style={{
          background:
            percentage >= 80
              ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
              : percentage >= 50
              ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'
              : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
        }}
      >
        <CardContent className="p-4 md:p-5 text-white">
          <div className="flex items-center justify-between mb-3">
            <h3 className="text-lg font-semibold flex items-center gap-2">
              {isComplete ? (
                <CheckCircle className="h-5 w-5" />
              ) : (
                <AlertCircle className="h-5 w-5" />
              )}
              Profile Completion
            </h3>
            <span className="text-2xl font-bold">{percentage}%</span>
          </div>

          <Progress value={percentage} className="h-2 mb-3 bg-white/20" />

          <p className="text-sm text-white/90 mb-3">
            {getCompletionMessage(percentage)}
          </p>

          {!isComplete && (
            <div className="flex items-center justify-between">
              <span className="text-xs text-white/80">
                {completedFields} of {totalFields} completed
              </span>
              <ArrowRight className="h-4 w-4" />
            </div>
          )}
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="shadow-xl border-0 rounded">
      <CardHeader className="pb-3">
        <CardTitle className="flex items-center justify-between">
          <span className="flex items-center gap-2">
            {isComplete ? (
              <CheckCircle className={`h-5 w-5 ${colorClass}`} />
            ) : (
              <AlertCircle className={`h-5 w-5 ${colorClass}`} />
            )}
            Profile Completion
          </span>
          <Badge
            variant={isComplete ? 'default' : 'secondary'}
            className={isComplete ? 'bg-green-600' : ''}
          >
            {percentage}%
          </Badge>
        </CardTitle>
      </CardHeader>

      <CardContent className="space-y-4">
        {/* Progress Bar */}
        <div>
          <div className="flex justify-between text-sm mb-2">
            <span className="text-gray-600">
              {completedFields} of {totalFields} fields completed
            </span>
            <span className={`font-semibold ${colorClass}`}>
              {percentage}%
            </span>
          </div>
          <Progress value={percentage} className="h-2" />
          <p className="text-sm text-gray-600 mt-2">
            {getCompletionMessage(percentage)}
          </p>
        </div>

        {/* Incomplete Sections */}
        {incompleteSections.length > 0 ? (
          <div className="space-y-3">
            <h4 className="font-semibold text-sm text-gray-700">
              Complete these sections:
            </h4>
            {incompleteSections.map(({ section, missingFields }) => {
              const IconComponent =
                SECTION_ICONS[section.icon as keyof typeof SECTION_ICONS] || User;

              return (
                <div
                  key={section.id}
                  className="p-3 bg-red-50 border border-red-100 rounded-lg"
                >
                  <div className="flex items-start gap-3">
                    <div className="p-2 bg-red-100 rounded-lg">
                      <IconComponent className="h-4 w-4 text-red-600" />
                    </div>
                    <div className="flex-1">
                      <h5 className="font-semibold text-sm text-gray-900 mb-1">
                        {section.label}
                      </h5>
                      <ul className="space-y-1">
                        {missingFields.map((field) => (
                          <li
                            key={field.key}
                            className="text-xs text-gray-600 flex items-center gap-1"
                          >
                            <span className="w-1 h-1 bg-red-400 rounded-full" />
                            {field.label}
                          </li>
                        ))}
                      </ul>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="p-4 bg-green-50 border border-green-200 rounded-lg text-center">
            <CheckCircle className="h-8 w-8 text-green-600 mx-auto mb-2" />
            <p className="text-sm font-semibold text-green-800">
              Profile Complete!
            </p>
            <p className="text-xs text-green-600 mt-1">
              All required information has been provided.
            </p>
          </div>
        )}

        {/* Action Button */}
        {!isComplete && onNavigate && (
          <Button
            onClick={() => onNavigate('profile')}
            className="w-full bg-[var(--brand-electric-blue)] hover:bg-[var(--brand-electric-blue)]/90"
          >
            Complete Your Profile
            <ArrowRight className="ml-2 h-4 w-4" />
          </Button>
        )}
      </CardContent>
    </Card>
  );
}
