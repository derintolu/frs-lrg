import { useState, useEffect } from 'react';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '../ui/dialog';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Badge } from '../ui/badge';
import { Checkbox } from '../ui/checkbox';
import { Progress } from '../ui/progress';
import {
  Sparkles,
  ArrowRight,
  ArrowLeft,
  Check,
  Building2,
  FileText,
  Calculator,
  BookOpen,
  User,
  Globe,
  CheckCircle2,
  XCircle,
  MapPin,
  Trophy,
  Users,
  ChevronRight,
} from 'lucide-react';

interface Century21WelcomeOnboardingProps {
  isOpen: boolean;
  onClose: () => void;
  currentUser: any;
  companyName: string;
  onCreateLandingPage?: () => void;
  onCompleteProfile?: () => void;
  onNavigate?: (path: string) => void;
}

interface OnboardingTask {
  id: string;
  title: string;
  description: string;
  icon: any;
  completed: boolean;
  action?: () => void;
  actionLabel?: string;
}

export function Century21WelcomeOnboarding({
  isOpen,
  onClose,
  currentUser,
  companyName,
  onCreateLandingPage,
  onCompleteProfile,
  onNavigate,
}: Century21WelcomeOnboardingProps) {
  const [step, setStep] = useState(0);
  const [tasks, setTasks] = useState<OnboardingTask[]>([
    {
      id: 'profile',
      title: 'Complete Your Profile',
      description: 'Add your company information, logo, and contact details',
      icon: User,
      completed: false,
      action: onCompleteProfile,
      actionLabel: 'Complete Profile',
    },
    {
      id: 'landing',
      title: 'Create Your First Co-Branded Page',
      description: 'Generate a professional landing page with your loan officer partner',
      icon: Globe,
      completed: false,
      action: onCreateLandingPage,
      actionLabel: 'Create Page',
    },
    {
      id: 'marketing',
      title: 'Download Marketing Materials',
      description: 'Access co-branded flyers, social media graphics, and email templates',
      icon: FileText,
      completed: false,
    },
    {
      id: 'calculator',
      title: 'Try the Mortgage Calculators',
      description: 'Explore all 7 mortgage calculators available to share with clients',
      icon: Calculator,
      completed: false,
    },
  ]);

  const completedTasks = tasks.filter(t => t.completed).length;
  const progress = (completedTasks / tasks.length) * 100;

  const toggleTask = (taskId: string) => {
    setTasks(tasks.map(task =>
      task.id === taskId ? { ...task, completed: !task.completed } : task
    ));
  };

  const steps = [
    {
      title: 'Welcome to Century 21 Professionals!',
      description: 'Your partnership with 21st Century Lending',
      content: (
        <div className="space-y-6 py-6">
          {/* Hero Section */}
          <div className="text-center space-y-4">
            <div className="flex justify-center">
              <div className="w-24 h-24 rounded-full bg-gradient-to-br from-[#D4AF37] to-[#FFD700] flex items-center justify-center shadow-2xl">
                <Trophy className="h-12 w-12 text-white" />
              </div>
            </div>
            <div>
              <h2 className="text-3xl font-bold text-gray-900 mb-2">
                Welcome to Your Partnership Portal
              </h2>
              <p className="text-lg text-gray-600">
                {currentUser.display_name || 'Partner'} at {companyName}
              </p>
            </div>
          </div>

          {/* Partnership Benefits */}
          <Card className="border-2 border-[#D4AF37]/20 bg-gradient-to-br from-white to-amber-50/30">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Sparkles className="h-5 w-5 text-[#D4AF37]" />
                Partnership Benefits
              </CardTitle>
            </CardHeader>
            <CardContent className="grid grid-cols-2 gap-4">
              <div className="flex items-start gap-3">
                <CheckCircle2 className="h-5 w-5 text-green-600 mt-1 flex-shrink-0" />
                <div>
                  <h4 className="font-semibold text-gray-900">Co-Branded Marketing</h4>
                  <p className="text-sm text-gray-600">Professional materials featuring both brands</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <CheckCircle2 className="h-5 w-5 text-green-600 mt-1 flex-shrink-0" />
                <div>
                  <h4 className="font-semibold text-gray-900">Lead Generation</h4>
                  <p className="text-sm text-gray-600">Landing pages to capture and convert leads</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <CheckCircle2 className="h-5 w-5 text-green-600 mt-1 flex-shrink-0" />
                <div>
                  <h4 className="font-semibold text-gray-900">Mortgage Tools</h4>
                  <p className="text-sm text-gray-600">7 calculators for your clients</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <CheckCircle2 className="h-5 w-5 text-green-600 mt-1 flex-shrink-0" />
                <div>
                  <h4 className="font-semibold text-gray-900">Resources Library</h4>
                  <p className="text-sm text-gray-600">Educational content and training materials</p>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Quick Stats */}
          <div className="grid grid-cols-3 gap-4">
            <Card>
              <CardContent className="pt-6 text-center">
                <div className="text-3xl font-bold text-[#D4AF37]">100+</div>
                <div className="text-sm text-gray-600 mt-1">Marketing Assets</div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6 text-center">
                <div className="text-3xl font-bold text-[#D4AF37]">7</div>
                <div className="text-sm text-gray-600 mt-1">Calculators</div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6 text-center">
                <div className="text-3xl font-bold text-[#D4AF37]">24/7</div>
                <div className="text-sm text-gray-600 mt-1">Portal Access</div>
              </CardContent>
            </Card>
          </div>
        </div>
      ),
    },
    {
      title: 'Portal Tour',
      description: 'Explore everything your portal has to offer',
      content: (
        <div className="space-y-4 py-6">
          <div className="grid grid-cols-1 gap-4">
            {/* Company Overview */}
            <Card
              className="hover:shadow-lg transition-shadow cursor-pointer border-l-4 border-l-blue-500"
              onClick={() => {
                if (onNavigate) {
                  onNavigate('/');
                  onClose();
                }
              }}
            >
              <CardHeader>
                <div className="flex items-start gap-4">
                  <div className="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <Building2 className="h-6 w-6 text-blue-600" />
                  </div>
                  <div className="flex-1">
                    <CardTitle className="text-lg">Company Overview</CardTitle>
                    <CardDescription>
                      Your partnership dashboard with stats, loan officer assignments, and partnership health metrics
                    </CardDescription>
                  </div>
                  <ChevronRight className="h-5 w-5 text-gray-400" />
                </div>
              </CardHeader>
            </Card>

            {/* Marketing Tools */}
            <Card
              className="hover:shadow-lg transition-shadow cursor-pointer border-l-4 border-l-[#D4AF37]"
              onClick={() => {
                if (onNavigate) {
                  onNavigate('/marketing');
                  onClose();
                }
              }}
            >
              <CardHeader>
                <div className="flex items-start gap-4">
                  <div className="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <FileText className="h-6 w-6 text-[#D4AF37]" />
                  </div>
                  <div className="flex-1">
                    <CardTitle className="text-lg">Marketing Tools</CardTitle>
                    <CardDescription>
                      Create co-branded landing pages, download marketing materials, and access social media graphics
                    </CardDescription>
                  </div>
                  <ChevronRight className="h-5 w-5 text-gray-400" />
                </div>
              </CardHeader>
            </Card>

            {/* Calculator & Tools */}
            <Card
              className="hover:shadow-lg transition-shadow cursor-pointer border-l-4 border-l-green-500"
              onClick={() => {
                if (onNavigate) {
                  onNavigate('/tools');
                  onClose();
                }
              }}
            >
              <CardHeader>
                <div className="flex items-start gap-4">
                  <div className="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <Calculator className="h-6 w-6 text-green-600" />
                  </div>
                  <div className="flex-1">
                    <CardTitle className="text-lg">Calculator & Tools</CardTitle>
                    <CardDescription>
                      7 mortgage calculators including payment, affordability, refinance, and investment property calculators
                    </CardDescription>
                  </div>
                  <ChevronRight className="h-5 w-5 text-gray-400" />
                </div>
              </CardHeader>
            </Card>

            {/* Resources */}
            <Card
              className="hover:shadow-lg transition-shadow cursor-pointer border-l-4 border-l-purple-500"
              onClick={() => {
                if (onNavigate) {
                  onNavigate('/resources');
                  onClose();
                }
              }}
            >
              <CardHeader>
                <div className="flex items-start gap-4">
                  <div className="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <BookOpen className="h-6 w-6 text-purple-600" />
                  </div>
                  <div className="flex-1">
                    <CardTitle className="text-lg">Resources</CardTitle>
                    <CardDescription>
                      Educational articles, training videos, best practices guides, and partnership documentation
                    </CardDescription>
                  </div>
                  <ChevronRight className="h-5 w-5 text-gray-400" />
                </div>
              </CardHeader>
            </Card>
          </div>

          {/* Navigation Tip */}
          <Card className="bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200">
            <CardContent className="pt-6">
              <div className="flex items-start gap-3">
                <MapPin className="h-5 w-5 text-blue-600 mt-1 flex-shrink-0" />
                <div>
                  <h4 className="font-semibold text-gray-900 mb-1">Navigation Tip</h4>
                  <p className="text-sm text-gray-700">
                    Use the sidebar menu on the left to navigate between sections. Your current section is highlighted.
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      ),
    },
    {
      title: 'Complete Your Profile',
      description: 'Help us personalize your experience',
      content: (
        <div className="space-y-6 py-6">
          <Card className="border-2 border-[#D4AF37]/20">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <User className="h-5 w-5 text-[#D4AF37]" />
                Profile Setup
              </CardTitle>
              <CardDescription>
                Complete your profile to get personalized marketing materials
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-3">
                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <Building2 className="h-5 w-5 text-gray-600" />
                    <div>
                      <div className="font-medium text-gray-900">Company Information</div>
                      <div className="text-sm text-gray-600">Name, logo, and contact details</div>
                    </div>
                  </div>
                  <Badge variant="secondary">Required</Badge>
                </div>

                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <Users className="h-5 w-5 text-gray-600" />
                    <div>
                      <div className="font-medium text-gray-900">Team Members</div>
                      <div className="text-sm text-gray-600">Add your agents and staff</div>
                    </div>
                  </div>
                  <Badge>Optional</Badge>
                </div>

                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div className="flex items-center gap-3">
                    <MapPin className="h-5 w-5 text-gray-600" />
                    <div>
                      <div className="font-medium text-gray-900">Service Areas</div>
                      <div className="text-sm text-gray-600">Cities and regions you serve</div>
                    </div>
                  </div>
                  <Badge>Optional</Badge>
                </div>
              </div>

              {onCompleteProfile && (
                <Button
                  onClick={() => {
                    onCompleteProfile();
                    onClose();
                  }}
                  className="w-full bg-[#D4AF37] hover:bg-[#C4A037] text-white"
                  size="lg"
                >
                  <User className="h-4 w-4 mr-2" />
                  Complete Profile Now
                </Button>
              )}
            </CardContent>
          </Card>
        </div>
      ),
    },
    {
      title: 'Getting Started Checklist',
      description: 'Complete these tasks to get the most out of your portal',
      content: (
        <div className="space-y-6 py-6">
          {/* Progress Bar */}
          <div className="space-y-2">
            <div className="flex justify-between items-center">
              <span className="text-sm font-medium text-gray-700">
                {completedTasks} of {tasks.length} completed
              </span>
              <span className="text-sm text-gray-600">{Math.round(progress)}%</span>
            </div>
            <Progress value={progress} className="h-3" />
          </div>

          {/* Task List */}
          <div className="space-y-3">
            {tasks.map((task) => {
              const Icon = task.icon;
              return (
                <Card
                  key={task.id}
                  className={`transition-all ${
                    task.completed
                      ? 'bg-green-50 border-green-200'
                      : 'hover:shadow-md cursor-pointer'
                  }`}
                >
                  <CardContent className="p-4">
                    <div className="flex items-start gap-4">
                      <div className="flex items-center gap-3 flex-1">
                        <Checkbox
                          checked={task.completed}
                          onCheckedChange={() => toggleTask(task.id)}
                          className="mt-1"
                        />
                        <div
                          className={`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${
                            task.completed ? 'bg-green-100' : 'bg-gray-100'
                          }`}
                        >
                          <Icon
                            className={`h-5 w-5 ${
                              task.completed ? 'text-green-600' : 'text-gray-600'
                            }`}
                          />
                        </div>
                        <div className="flex-1">
                          <h4
                            className={`font-semibold ${
                              task.completed ? 'text-green-900 line-through' : 'text-gray-900'
                            }`}
                          >
                            {task.title}
                          </h4>
                          <p
                            className={`text-sm ${
                              task.completed ? 'text-green-700' : 'text-gray-600'
                            }`}
                          >
                            {task.description}
                          </p>
                        </div>
                      </div>
                      {!task.completed && task.action && (
                        <Button
                          onClick={() => {
                            task.action?.();
                            toggleTask(task.id);
                            onClose();
                          }}
                          size="sm"
                          variant="outline"
                          className="flex-shrink-0"
                        >
                          {task.actionLabel}
                          <ArrowRight className="h-4 w-4 ml-2" />
                        </Button>
                      )}
                      {task.completed && (
                        <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                      )}
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>

          {/* Completion Message */}
          {completedTasks === tasks.length && (
            <Card className="bg-gradient-to-r from-green-50 to-emerald-50 border-green-200">
              <CardContent className="pt-6">
                <div className="text-center space-y-3">
                  <div className="flex justify-center">
                    <div className="w-16 h-16 rounded-full bg-green-500 flex items-center justify-center">
                      <Trophy className="h-8 w-8 text-white" />
                    </div>
                  </div>
                  <div>
                    <h3 className="text-xl font-bold text-gray-900">All Set!</h3>
                    <p className="text-gray-700">
                      You&apos;ve completed all the onboarding tasks. You&apos;re ready to maximize your partnership!
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      ),
    },
  ];

  const currentStep = steps[step];
  const isLastStep = step === steps.length - 1;
  const isFirstStep = step === 0;

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-[#D4AF37] to-[#FFD700] flex items-center justify-center">
                <Sparkles className="h-5 w-5 text-white" />
              </div>
              <div>
                <DialogTitle className="text-2xl">{currentStep.title}</DialogTitle>
                <DialogDescription>{currentStep.description}</DialogDescription>
              </div>
            </div>
          </div>

          {/* Step Indicator */}
          <div className="flex items-center gap-2 mt-4">
            {steps.map((_, index) => (
              <div
                key={index}
                className={`flex-1 h-1 rounded-full transition-all ${
                  index <= step ? 'bg-[#D4AF37]' : 'bg-gray-200'
                }`}
              />
            ))}
          </div>
        </DialogHeader>

        {/* Step Content */}
        <div className="min-h-[400px]">
          {currentStep.content}
        </div>

        {/* Footer Navigation */}
        <div className="flex justify-between items-center pt-6 border-t">
          <Button
            variant="outline"
            onClick={() => setStep(step - 1)}
            disabled={isFirstStep}
          >
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back
          </Button>

          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-600">
              Step {step + 1} of {steps.length}
            </span>
          </div>

          <div className="flex gap-2">
            <Button variant="ghost" onClick={onClose}>
              Skip for Now
            </Button>
            {!isLastStep ? (
              <Button
                onClick={() => setStep(step + 1)}
                className="bg-[#D4AF37] hover:bg-[#C4A037] text-white"
              >
                Next
                <ArrowRight className="h-4 w-4 ml-2" />
              </Button>
            ) : (
              <Button
                onClick={onClose}
                className="bg-green-600 hover:bg-green-700 text-white"
              >
                <Check className="h-4 w-4 mr-2" />
                Get Started
              </Button>
            )}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
