import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { FloatingInput } from './ui/floating-input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';
import { Badge } from './ui/badge';
// Avatar components removed - using simple div implementations
import { Switch } from './ui/switch';
import { Separator } from './ui/separator';
import {
  User,
  Mail,
  Phone,
  MapPin,
  Building,
  Camera,
  Upload,
  Edit,
  Save,
  X,
  Globe,
  Linkedin,
  Facebook,
  Settings,
  Shield,
  Bell,
  FileText,
  ExternalLink,
  CheckSquare,
  Link,
  Smartphone,
  QrCode
} from 'lucide-react';
import { ImageWithFallback } from './figma/ImageWithFallback';
import { LoadingSpinner } from './ui/loading';
import { DataService } from '../utils/dataService';
import { ProfileTour, TourTrigger } from './ProfileTour';
import { ProfileCompletionSection } from './loan-officer-portal/ProfileCompletionSection';

// Read-only field display component
const ReadOnlyField = ({ icon: Icon, value, label }: { icon: any, value: string, label?: string }) => (
  <div className="flex items-center space-x-3 p-3 bg-gray-50 rounded-md border">
    <Icon className="h-4 w-4 text-[var(--brand-slate)] flex-shrink-0" />
    <span className="text-[var(--brand-dark-navy)]">{value}</span>
  </div>
);

const ReadOnlyTextarea = ({ icon: Icon, value }: { icon: any, value: string }) => (
  <div className="flex items-start space-x-3 py-2">
    <Icon className="h-4 w-4 text-blue-600 flex-shrink-0 mt-1" />
    <div className="text-gray-900 whitespace-pre-wrap leading-relaxed">{value}</div>
  </div>
);

interface ProfileSectionProps {
  userRole: 'loan-officer' | 'realtor';
  userId: string;
  activeTab?: 'welcome' | 'personal' | 'settings';
  autoEdit?: boolean;
  tourAttributes?: {
    announcements?: string;
    profileSummary?: string;
  };
}

export function ProfileSection({ userRole, userId, activeTab: externalActiveTab, autoEdit = false, tourAttributes }: ProfileSectionProps) {
  const [activeTab, setActiveTab] = useState(externalActiveTab || 'welcome');
  const [isEditing, setIsEditing] = useState(autoEdit);
  const [isSaving, setIsSaving] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showQRCode, setShowQRCode] = useState(false);

  // Update editing state when autoEdit prop changes
  useEffect(() => {
    setIsEditing(autoEdit);
  }, [autoEdit]);

  // User profile data - initialize with empty values, load from WordPress
  const [profileData, setProfileData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    mobileNumber: '',
    title: '',
    company: '',
    nmls: '',
    nmls_number: '',
    license_number: '',
    dre_license: '',
    location: '',
    bio: '',
    website: '',
    linkedin: '',
    facebook: '',
    instagram: '',
    twitter: '',
    youtube: '',
    tiktok: '',
    profileImage: '',
    specialtiesLo: [] as string[],
    specialties: [] as string[],
    languages: [] as string[],
    awards: [] as string[],
    nambCertifications: [] as string[],
    narDesignations: [] as string[],
    brand: '',
    arrive: '',
    canvaFolderLink: '',
    nicheBioContent: ''
  });

  // Person CPT data
  const [personCPTData, setPersonCPTData] = useState<any>(null);
  const [hasPersonCPT, setHasPersonCPT] = useState(false);

  // Welcome tab data
  const [announcements, setAnnouncements] = useState<any[]>([]);
  const [customLinks, setCustomLinks] = useState<any[]>([]);

  // Tour state
  const [isTourOpen, setIsTourOpen] = useState(false);
  const [selectedAnnouncement, setSelectedAnnouncement] = useState<any>(null);
  const [isAnnouncementModalOpen, setIsAnnouncementModalOpen] = useState(false);

  // Load user profile data
  useEffect(() => {
    const loadProfile = async () => {
      try {
        setLoading(true);

        // Load basic user data
        const user = await DataService.getCurrentUser();

        // Try to load Person CPT data
        try {
          const personResponse = await fetch('/wp-json/frs/v1/users/me/person-profile', {
            headers: {
              'X-WP-Nonce': (window as any).frsPortalData?.nonce || ''
            }
          });

          if (personResponse.ok) {
            const personData = await personResponse.json();
            setPersonCPTData(personData);
            setHasPersonCPT(personData.has_person_cpt);

            if (personData.has_person_cpt) {
              // Use Person CPT data when available
              const nameParts = personData.name?.split(' ') || [];
              setProfileData(prev => ({
                ...prev,
                firstName: nameParts[0] || prev.firstName,
                lastName: nameParts.slice(1).join(' ') || prev.lastName,
                email: personData.primary_business_email || prev.email,
                phone: personData.phone_number || prev.phone,
                title: personData.job_title || prev.title,
                bio: personData.biography || prev.bio,
                linkedin: personData.linkedin_url || prev.linkedin,
                facebook: personData.facebook_url || prev.facebook,
                profileImage: personData.headshot || prev.profileImage,
                company: '21st Century Lending', // Fixed company for all users
                specialtiesLo: Array.isArray(personData.specialties_lo) ? personData.specialties_lo : [],
                nambCertifications: Array.isArray(personData.namb_certifications) ? personData.namb_certifications : []
              }));
            }
          }
        } catch (personError) {
          console.log('Person CPT data not available:', personError);
        }

        // Set base user data
        if (user) {
          const nameParts = user.name?.split(' ') || [];
          setProfileData(prev => ({
            ...prev,
            firstName: prev.firstName || nameParts[0] || '',
            lastName: prev.lastName || nameParts.slice(1).join(' ') || '',
            email: prev.email || user.email || '',
            phone: prev.phone || user.phone || '',
            mobileNumber: prev.mobileNumber || user.mobile_number || '',
            title: prev.title || user.title || user.job_title || '',
            company: user.company || user.office || '21st Century Lending',
            nmls: user.nmls || user.nmls_number || '',
            nmls_number: user.nmls_number || user.nmls || '',
            license_number: user.license_number || '',
            dre_license: user.dre_license || '',
            location: prev.location || user.location || user.city_state || '',
            bio: prev.bio || user.biography || '',
            website: user.website || '',
            linkedin: prev.linkedin || user.linkedin_url || '',
            facebook: prev.facebook || user.facebook_url || '',
            instagram: prev.instagram || user.instagram_url || '',
            twitter: prev.twitter || user.twitter_url || '',
            youtube: prev.youtube || user.youtube_url || '',
            tiktok: prev.tiktok || user.tiktok_url || '',
            profileImage: prev.profileImage || user.avatar || user.headshot_url || '',
            specialtiesLo: user.specialties_lo || prev.specialtiesLo || [],
            specialties: user.specialties || prev.specialties || [],
            languages: user.languages || prev.languages || [],
            awards: user.awards || prev.awards || [],
            nambCertifications: user.namb_certifications || prev.nambCertifications || [],
            narDesignations: user.nar_designations || prev.narDesignations || [],
            brand: user.brand || prev.brand || '',
            arrive: user.arrive || prev.arrive || '',
            canvaFolderLink: user.canva_folder_link || prev.canvaFolderLink || '',
            nicheBioContent: user.niche_bio_content || prev.nicheBioContent || ''
          }));
        }
      } catch (error) {
        console.error('Failed to load profile:', error);
        setError('Failed to load profile data');
      } finally {
        setLoading(false);
      }
    };

    loadProfile();
  }, [userId, userRole]);

  // Load announcements and custom links
  useEffect(() => {
    const loadWelcomeData = async () => {
      try {
        // Load announcements
        const announcementsResponse = await fetch('/wp-json/frs/v1/announcements', {
          headers: {
            'X-WP-Nonce': (window as any).frsPortalData?.nonce || ''
          }
        });
        if (announcementsResponse.ok) {
          const announcementsData = await announcementsResponse.json();
          setAnnouncements(announcementsData);
        }

        // Load custom links
        const linksResponse = await fetch('/wp-json/frs/v1/custom-links', {
          headers: {
            'X-WP-Nonce': (window as any).frsPortalData?.nonce || ''
          }
        });
        if (linksResponse.ok) {
          const linksData = await linksResponse.json();
          setCustomLinks(linksData);
        }
      } catch (error) {
        console.error('Failed to load welcome data:', error);
      }
    };

    loadWelcomeData();
  }, []);

  const handleSave = async () => {
    setIsSaving(true);
    setError(null);
    try {
      console.log('Saving profile data:', profileData);
      const success = await DataService.updateUserProfile(userId, profileData);
      console.log('Save result:', success);
      if (success) {
        setIsEditing(false);
        // Show success message briefly
        const successMsg = document.createElement('div');
        successMsg.className = 'fixed top-20 right-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        successMsg.textContent = 'Profile saved successfully!';
        document.body.appendChild(successMsg);
        setTimeout(() => successMsg.remove(), 3000);
      } else {
        setError('Failed to save profile changes');
      }
    } catch (error) {
      console.error('Failed to save profile:', error);
      setError('Failed to save profile changes');
    } finally {
      setIsSaving(false);
    }
  };

  const handleAvatarUpload = async (file: File) => {
    try {
      const avatarUrl = await DataService.uploadAvatar(userId, file);
      if (avatarUrl) {
        setProfileData(prev => ({ ...prev, profileImage: avatarUrl }));
      }
    } catch (error) {
      console.error('Failed to upload avatar:', error);
      setError('Failed to upload avatar');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center py-8">
        <p className="text-red-600 mb-4">{error}</p>
        <Button onClick={() => window.location.reload()}>
          Try Again
        </Button>
      </div>
    );
  }



  // Use external activeTab if provided, otherwise use internal state
  const currentTab = externalActiveTab || activeTab;



  // Map profileData to the format expected by ProfileCompletionSection
  const completionData = {
    first_name: profileData.firstName,
    last_name: profileData.lastName,
    email: profileData.email,
    phone: profileData.phone,
    job_title: profileData.title,
    company: profileData.company,
    nmls_id: profileData.nmls,
    bio: profileData.bio,
    linkedin_url: profileData.linkedin,
    facebook_url: profileData.facebook,
    instagram_url: profileData.instagram,
  };

  return (
    <div className="space-y-4" style={{ marginTop: '-30px' }}>
      {/* Profile Completion Section - Shows on all tabs */}
      <div style={{ marginBottom: '10px' }}>
        <ProfileCompletionSection userData={completionData} />
      </div>

      {/* Tab Content - Show based on currentTab */}
      {currentTab === 'welcome' && (
        <div className="space-y-4 mt-4">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Profile Details Card */}
            <Card className="border-[var(--brand-powder-blue)]" data-tour={tourAttributes?.profileSummary}>
              <CardContent className="p-6">
                <div className="space-y-6">
                  {/* Profile Header */}
                  <div className="flex items-start space-x-4">
                    <div className="w-20 h-20 rounded-full flex items-center justify-center flex-shrink-0" style={{
                      background: 'linear-gradient(135deg, #5ce1e6, #3851DD)',
                      padding: '2px'
                    }}>
                      <div className="w-full h-full rounded-full overflow-hidden bg-[var(--brand-pale-blue)] flex items-center justify-center">
                        {profileData.profileImage ? (
                          <img src={profileData.profileImage} alt="Profile" className="w-full h-full object-cover" />
                        ) : (
                          <span className="text-xl text-[var(--brand-dark-navy)] font-semibold">
                            {(profileData.firstName?.[0] || '?')}{(profileData.lastName?.[0] || '')}
                          </span>
                        )}
                      </div>
                    </div>
                    <div className="flex-1 min-w-0">
                      <h3 className="text-lg font-semibold text-[var(--brand-dark-navy)] mb-1">
                        {profileData.firstName} {profileData.lastName}
                      </h3>
                      <p className="text-[var(--brand-slate)] mb-1">{profileData.title}</p>
                      <p className="text-sm text-[var(--brand-slate)]">
                        {profileData.company}
                        {profileData.nmls && <span className="ml-2">• NMLS #{profileData.nmls}</span>}
                      </p>
                    </div>
                  </div>

                  {/* Contact Info Grid */}
                  <div className="grid grid-cols-1 gap-3">
                    <div className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                      <Mail className="h-4 w-4 text-blue-600 flex-shrink-0" />
                      <span className="text-sm font-medium truncate">{profileData.email}</span>
                    </div>
                    <div className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                      <Phone className="h-4 w-4 text-green-600 flex-shrink-0" />
                      <span className="text-sm font-medium">{profileData.phone || 'Not provided'}</span>
                    </div>
                    {profileData.location && (
                      <div className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        <MapPin className="h-4 w-4 text-red-600 flex-shrink-0" />
                        <span className="text-sm font-medium">{profileData.location}</span>
                      </div>
                    )}
                  </div>

                  {/* Arrive Link as text */}
                  {personCPTData?.arrive && (
                    <div className="p-3 bg-blue-50 rounded-lg border border-blue-200">
                      <div className="flex items-center space-x-2 mb-1">
                        <Globe className="h-4 w-4 text-blue-600" />
                        <span className="text-sm font-medium text-blue-900">Arrive Registration</span>
                      </div>
                      <p className="text-xs text-blue-700 break-all ml-6">{personCPTData.arrive}</p>
                    </div>
                  )}

                  {/* Professional Links */}
                  {(profileData.linkedin || profileData.facebook || profileData.website) && (
                    <div className="flex space-x-2">
                      {profileData.linkedin && (
                        <Button size="sm" variant="outline" onClick={() => window.open(profileData.linkedin, '_blank')}>
                          <Linkedin className="h-4 w-4" />
                        </Button>
                      )}
                      {profileData.facebook && (
                        <Button size="sm" variant="outline" onClick={() => window.open(profileData.facebook, '_blank')}>
                          <Facebook className="h-4 w-4" />
                        </Button>
                      )}
                      {profileData.website && (
                        <Button size="sm" variant="outline" onClick={() => window.open(profileData.website, '_blank')}>
                          <Globe className="h-4 w-4" />
                        </Button>
                      )}
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Announcements Card */}
            <Card className="border-[var(--brand-powder-blue)]" data-tour={tourAttributes?.announcements}>
              <CardHeader className="h-12 flex items-center px-4 rounded-t-lg" style={{ backgroundColor: '#B6C7D9' }}>
                <CardTitle className="flex items-center gap-1 text-gray-700 text-sm">
                  <Bell className="h-3 w-3" />
                  Announcements
                </CardTitle>
              </CardHeader>
              <CardContent>
                {announcements.length > 0 ? (
                  <div className="space-y-3">
                    {announcements.slice(0, 3).map((announcement) => (
                      <div
                        key={announcement.id}
                        className="p-4 rounded-lg cursor-pointer transition-all hover:shadow-md border-l-4 bg-gray-50 border-l-blue-500 hover:bg-gray-100"
                        onClick={() => {
                          setSelectedAnnouncement(announcement);
                          setIsAnnouncementModalOpen(true);
                        }}
                      >
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="flex items-center space-x-2 mb-2">
                              <h4 className="font-medium text-[var(--brand-dark-navy)] text-sm">
                                {announcement.title}
                              </h4>
                              {announcement.priority === 'high' && (
                                <Badge variant="destructive" className="text-xs px-2 py-1">
                                  Priority
                                </Badge>
                              )}
                            </div>
                            <p className="text-xs text-[var(--brand-slate)] mb-2 line-clamp-2">
                              {announcement.excerpt}
                            </p>
                            <div className="flex items-center justify-between">
                              <p className="text-xs text-[var(--brand-slate)]">
                                {new Date(announcement.date).toLocaleDateString()}
                              </p>
                              <div className={`w-2 h-2 rounded-full ${
                                announcement.priority === 'high' ? 'bg-red-500' : 'bg-blue-500'
                              }`}></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                    {announcements.length > 3 && (
                      <div className="text-center pt-2">
                        <p className="text-xs text-[var(--brand-slate)]">
                          +{announcements.length - 3} more announcements
                        </p>
                      </div>
                    )}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Bell className="h-8 w-8 text-gray-300 mx-auto mb-2" />
                    <p className="text-sm text-[var(--brand-slate)]">
                      No announcements at this time
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Custom Links Section */}
          {customLinks.length > 0 && (
            <Card className="border-[var(--brand-powder-blue)]">
              <CardHeader className="h-12 flex items-center px-4 rounded-t-lg" style={{ backgroundColor: '#B6C7D9' }}>
                <CardTitle className="flex items-center gap-1 text-gray-700 text-sm">
                  <Globe className="h-3 w-3" />
                  Quick Links
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  {customLinks.map((link) => (
                    <div
                      key={link.id}
                      className="p-4 border rounded-lg cursor-pointer hover:shadow-md transition-shadow group"
                      onClick={() => window.open(link.url, '_blank')}
                      style={{ borderColor: link.color + '20', backgroundColor: link.color + '05' }}
                    >
                      <div className="flex items-start space-x-3">
                        <div
                          className="w-10 h-10 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform"
                          style={{ backgroundColor: link.color + '15' }}
                        >
                          <span style={{ color: link.color }}>🔗</span>
                        </div>
                        <div className="flex-1 min-w-0">
                          <h4 className="font-medium text-[var(--brand-dark-navy)] text-sm truncate">
                            {link.title}
                          </h4>
                          <p className="text-xs text-[var(--brand-slate)] mt-1 line-clamp-2">
                            {link.description}
                          </p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      )}

      {/* Personal Information Tab */}
      {currentTab === 'personal' && (
        <div className="space-y-4" style={{ paddingTop: '30px' }}>
          {/* Floating Edit/Save/Cancel Button - Bottom Right */}
          <div className="fixed bottom-6 right-6 z-50 flex items-center gap-3">
            {isEditing ? (
              <>
                {/* Cancel Button */}
                <Button
                  variant="outline"
                  onClick={() => setIsEditing(false)}
                  disabled={isSaving}
                  size="lg"
                  className="shadow-lg hover:shadow-xl transition-shadow bg-white"
                >
                  <X className="h-4 w-4 mr-2" />
                  Cancel
                </Button>
                {/* Save Button */}
                <Button
                  onClick={handleSave}
                  className="bg-[var(--brand-electric-blue)] hover:bg-[var(--brand-electric-blue)]/90 text-white shadow-lg hover:shadow-xl transition-shadow"
                  disabled={isSaving}
                  size="lg"
                >
                  {isSaving ? (
                    <>
                      <LoadingSpinner size="sm" className="mr-2" />
                      Saving...
                    </>
                  ) : (
                    <>
                      <Save className="h-4 w-4 mr-2" />
                      Save Changes
                    </>
                  )}
                </Button>
              </>
            ) : (
              /* Edit Button */
              <Button
                onClick={() => setIsEditing(true)}
                className="bg-[var(--brand-electric-blue)] hover:bg-[var(--brand-electric-blue)]/90 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105"
                size="lg"
              >
                <Edit className="h-4 w-4 mr-2" />
                Edit Profile
              </Button>
            )}
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:grid-rows-2">
            {/* Header Card - Profile Overview - Spans both rows */}
            <div className="lg:row-span-2">
              <Card className="shadow-lg rounded-2xl border border-gray-200 h-full max-h-[calc(100vh-120px)] overflow-hidden">
                {/* Light Gray Header - Figma Design */}
                <div
                  className="p-8 text-center relative overflow-hidden"
                  style={{
                    background: '#F4F4F5',
                  }}
                >
                  {/* Gradient Video Background - Blurred */}
                  <div className="absolute inset-0 w-full h-36 overflow-hidden">
                    <video
                      autoPlay
                      loop
                      muted
                      playsInline
                      className="w-full h-full object-cover"
                      style={{
                        filter: 'blur(30px)',
                        boxShadow: '60px 60px 60px rgba(0,0,0,0.3)',
                        transform: 'scale(1.2)'
                      }}
                    >
                      <source src={(window as any).frsPortalConfig?.gradientUrl} type="video/mp4" />
                    </video>
                  </div>

                  {/* Avatar with Gradient Border - Flip Card */}
                  <div className="flex justify-center mb-4 relative z-10" style={{ perspective: '1000px' }}>
                    <div
                      className="relative transition-transform duration-700"
                      style={{
                        width: '156px',
                        height: '156px',
                        transformStyle: 'preserve-3d',
                        transform: showQRCode ? 'rotateY(180deg)' : 'rotateY(0deg)'
                      }}
                    >
                      {/* Front Side - Avatar */}
                      <div
                        className="absolute inset-0 rounded-full p-1"
                        style={{
                          background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
                          backfaceVisibility: 'hidden'
                        }}
                      >
                        <div className="w-full h-full rounded-full overflow-hidden bg-white">
                          {profileData.profileImage ? (
                            <img src={profileData.profileImage} alt="Profile" className="w-full h-full object-cover" />
                          ) : (
                            <div className="w-full h-full flex items-center justify-center bg-gray-100">
                              <span className="text-3xl text-gray-600 font-semibold">
                                {(profileData.firstName?.[0] || '?')}{(profileData.lastName?.[0] || '')}
                              </span>
                            </div>
                          )}
                        </div>
                      </div>

                      {/* Back Side - QR Code */}
                      <div
                        className="absolute inset-0 rounded-full p-1 flex items-center justify-center bg-white"
                        style={{
                          backfaceVisibility: 'hidden',
                          transform: 'rotateY(180deg)'
                        }}
                      >
                        <div className="w-full h-full flex items-center justify-center p-8">
                          <img
                            src={`https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(`BEGIN:VCARD\nVERSION:3.0\nFN:${profileData.firstName} ${profileData.lastName}\nTEL:${profileData.phone}\nEMAIL:${profileData.email}\nTITLE:${profileData.title}\nORG:${profileData.company}\nEND:VCARD`)}`}
                            alt="QR Code"
                            className="w-full h-full object-contain"
                          />
                        </div>
                      </div>
                    </div>

                    {/* QR Code button - top-right (always visible) */}
                    <Button
                      size="sm"
                      className="absolute top-2 right-2 rounded-full w-10 h-10 p-0 bg-black text-white hover:bg-gray-900 shadow-lg z-20"
                      onClick={() => setShowQRCode(!showQRCode)}
                      type="button"
                    >
                      <QrCode className="h-5 w-5" />
                    </Button>

                    {/* Camera button - top-left (only in edit mode) */}
                    {isEditing && (
                      <>
                        <input
                          type="file"
                          id="avatar-upload"
                          accept="image/*"
                          className="hidden"
                          onChange={(e) => {
                            const file = e.target.files?.[0];
                            if (file) {
                              handleAvatarUpload(file);
                            }
                          }}
                        />
                        <Button
                          size="sm"
                          className="absolute -top-0 left-2 rounded-full w-10 h-10 p-0 bg-black text-white hover:bg-gray-900 shadow-lg z-20"
                          onClick={() => document.getElementById('avatar-upload')?.click()}
                          type="button"
                        >
                          <Camera className="h-5 w-5" />
                        </Button>
                      </>
                    )}
                  </div>

                  {/* Name */}
                  {isEditing ? (
                    <div className="grid grid-cols-2 gap-2 mb-4 relative z-10">
                      <FloatingInput
                        id="firstName-profile"
                        label="First Name"
                        value={profileData.firstName}
                        onChange={(e) => setProfileData({...profileData, firstName: e.target.value})}
                        className="bg-white/90"
                      />
                      <FloatingInput
                        id="lastName-profile"
                        label="Last Name"
                        value={profileData.lastName}
                        onChange={(e) => setProfileData({...profileData, lastName: e.target.value})}
                        className="bg-white/90"
                      />
                    </div>
                  ) : (
                    <h3 className="text-[34px] font-bold text-[#1A1A1A] mb-2 relative z-10" style={{ fontFamily: 'Roboto, sans-serif' }}>
                      {profileData.firstName} {profileData.lastName}
                    </h3>
                  )}

                  {/* Job Title and Company */}
                  {!isEditing && (
                    <div className="mb-2 relative z-10">
                      <p className="text-base text-[#1D4FC4]" style={{ fontFamily: 'Roboto, sans-serif' }}>
                        {profileData.title || (userRole === 'loan-officer' ? 'Loan Officer' : 'Realtor Partner')}
                        {profileData.company && <span className="text-[#1A1A1A]"> at {profileData.company}</span>}
                      </p>
                    </div>
                  )}

                  {/* Location */}
                  {!isEditing && profileData.location && (
                    <div className="flex items-center justify-center gap-2 mb-4 relative z-10">
                      <MapPin className="h-4 w-4 text-[#1D4FC4]" />
                      <span className="text-base text-[#1D4FC4]" style={{ fontFamily: 'Roboto, sans-serif' }}>
                        {profileData.location}
                      </span>
                    </div>
                  )}

                  {/* NMLS */}
                  {!isEditing && profileData.nmls && (
                    <div className="mb-4 relative z-10">
                      <span className="text-sm text-[#1A1A1A]" style={{ fontFamily: 'Roboto, sans-serif' }}>
                        NMLS #{profileData.nmls}
                      </span>
                    </div>
                  )}

                  {/* Social Media Icons Row */}
                  {!isEditing && (profileData.linkedin || profileData.facebook || profileData.instagram || profileData.twitter || profileData.youtube || profileData.website) && (
                    <div className="flex items-center justify-center gap-3 mb-4 relative z-10">
                      {profileData.linkedin && (
                        <a href={profileData.linkedin} target="_blank" rel="noopener noreferrer">
                          <Linkedin className="h-6 w-6 text-[#1A1A1A] hover:text-[#2563eb] transition-colors" />
                        </a>
                      )}
                      {profileData.facebook && (
                        <a href={profileData.facebook} target="_blank" rel="noopener noreferrer">
                          <Facebook className="h-6 w-6 text-[#1A1A1A] hover:text-[#2563eb] transition-colors" />
                        </a>
                      )}
                      {profileData.instagram && (
                        <a href={profileData.instagram} target="_blank" rel="noopener noreferrer">
                          <Smartphone className="h-6 w-6 text-[#1A1A1A] hover:text-[#2563eb] transition-colors" />
                        </a>
                      )}
                      {profileData.twitter && (
                        <a href={profileData.twitter} target="_blank" rel="noopener noreferrer">
                          <Globe className="h-6 w-6 text-[#1A1A1A] hover:text-[#2563eb] transition-colors" />
                        </a>
                      )}
                      {profileData.youtube && (
                        <a href={profileData.youtube} target="_blank" rel="noopener noreferrer">
                          <Globe className="h-6 w-6 text-[#1A1A1A] hover:text-[#2563eb] transition-colors" />
                        </a>
                      )}
                      {profileData.website && (
                        <a href={profileData.website} target="_blank" rel="noopener noreferrer">
                          <Globe className="h-6 w-6 text-[#1A1A1A] hover:text-[#2563eb] transition-colors" />
                        </a>
                      )}
                    </div>
                  )}

                  {/* Bio Preview or Placeholder */}
                  {!isEditing && (
                    <div className="mb-4 px-4 relative z-10">
                      <p
                        className={`text-base ${profileData.bio ? 'text-[#1E1E1E]' : 'text-[#1E1E1E] opacity-50'}`}
                        style={{
                          fontFamily: 'Roboto, sans-serif',
                          lineHeight: '22.4px'
                        }}
                      >
                        {profileData.bio || 'Add a short bio. Tell the world who you are and what you do.'}
                      </p>
                    </div>
                  )}

                  {/* Edit Mode Fields */}
                  {isEditing && (
                    <div className="space-y-3 relative z-10">
                      <FloatingInput
                        id="email-profile"
                        label="Email"
                        type="email"
                        value={profileData.email}
                        onChange={(e) => setProfileData({...profileData, email: e.target.value})}
                        className="bg-white/90"
                      />
                      <FloatingInput
                        id="title-edit"
                        label="Job Title"
                        value={profileData.title}
                        onChange={(e) => setProfileData({...profileData, title: e.target.value})}
                        className="bg-white/90"
                      />
                      <FloatingInput
                        id="location-edit"
                        label="Location"
                        value={profileData.location}
                        onChange={(e) => setProfileData({...profileData, location: e.target.value})}
                        className="bg-white/90"
                      />
                      <Textarea
                        id="bio-edit"
                        value={profileData.bio}
                        onChange={(e) => setProfileData({...profileData, bio: e.target.value})}
                        className="bg-white/90 min-h-[100px]"
                        placeholder="Add a short bio. Tell the world who you are and what you do."
                      />
                      <div className="grid grid-cols-2 gap-2">
                        <FloatingInput
                          id="linkedin-edit"
                          label="LinkedIn URL"
                          type="url"
                          value={profileData.linkedin}
                          onChange={(e) => setProfileData({...profileData, linkedin: e.target.value})}
                          className="bg-white/90"
                        />
                        <FloatingInput
                          id="facebook-edit"
                          label="Facebook URL"
                          type="url"
                          value={profileData.facebook}
                          onChange={(e) => setProfileData({...profileData, facebook: e.target.value})}
                          className="bg-white/90"
                        />
                        <FloatingInput
                          id="instagram-edit"
                          label="Instagram URL"
                          type="url"
                          value={profileData.instagram}
                          onChange={(e) => setProfileData({...profileData, instagram: e.target.value})}
                          className="bg-white/90"
                        />
                        <FloatingInput
                          id="website-edit"
                          label="Website URL"
                          type="url"
                          value={profileData.website}
                          onChange={(e) => setProfileData({...profileData, website: e.target.value})}
                          className="bg-white/90"
                        />
                      </div>
                    </div>
                  )}
                </div>

                {/* Separator */}
                <Separator className="my-0" />

                {/* White Bottom Section - Bento Cards */}
                <CardContent className="p-6 bg-white space-y-4">
                  {/* Job Title Card */}
                  <div>
                    {!isEditing && <div className="text-sm font-semibold text-gray-900 mb-2">Job Title</div>}
                    {isEditing ? (
                      <FloatingInput
                        id="title"
                        label="Job Title"
                        value={profileData.title}
                        onChange={(e) => setProfileData({...profileData, title: e.target.value})}
                      />
                    ) : (
                      <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <User className="h-4 w-4 text-gray-700" />
                        <span className="text-sm font-medium text-gray-900">
                          {profileData.title || 'Not set'}
                        </span>
                      </div>
                    )}
                  </div>

                  {/* Phone Number and Location - Side by Side */}
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      {!isEditing && <div className="text-sm font-semibold text-gray-900 mb-2">Phone Number</div>}
                      {isEditing ? (
                        <FloatingInput
                          id="phone"
                          label="Phone Number"
                          type="tel"
                          value={profileData.phone}
                          onChange={(e) => setProfileData({...profileData, phone: e.target.value})}
                        />
                      ) : (
                        <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                          <Phone className="h-4 w-4 text-gray-700" />
                          <span className="text-sm font-medium text-gray-900">
                            {profileData.phone || 'Not set'}
                          </span>
                        </div>
                      )}
                    </div>

                    <div>
                      {!isEditing && <div className="text-sm font-semibold text-gray-900 mb-2">Location</div>}
                      {isEditing ? (
                        <FloatingInput
                          id="location"
                          label="Location"
                          value={profileData.location}
                          onChange={(e) => setProfileData({...profileData, location: e.target.value})}
                        />
                      ) : (
                        <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                          <MapPin className="h-4 w-4 text-gray-700" />
                          <span className="text-sm font-medium text-gray-900">
                            {profileData.location || 'Not set'}
                          </span>
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Arrive Registration Card */}
                  <div>
                    {!isEditing && (
                      <div className="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                        <ExternalLink className="h-4 w-4" />
                        Arrive Registration
                      </div>
                    )}
                    <div className="flex items-center gap-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                      <Globe className="h-4 w-4 text-blue-600 flex-shrink-0" />
                      <a
                        href={personCPTData?.arrive || 'https://21stcenturylending.my1003app.com/1283065/register'}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-sm text-blue-600 hover:text-blue-800 flex-1 truncate font-medium"
                      >
                        {personCPTData?.arrive || 'https://21stcenturylending.my1003app.com/1283065/regi...'}
                      </a>
                      <Button
                        size="sm"
                        onClick={() => window.open(personCPTData?.arrive || 'https://21stcenturylending.my1003app.com/1283065/register', '_blank')}
                        className="flex-shrink-0 h-8 px-4 bg-blue-600 hover:bg-blue-700 text-white"
                      >
                        Open
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Biography Card */}
            <Card className="shadow-lg rounded-2xl border border-gray-200 h-full">
              <CardHeader className="h-12 flex items-center px-4 rounded-t-lg border-b border-gray-200">
                <CardTitle className="flex items-center gap-2 text-gray-900 text-sm font-semibold">
                  <FileText className="h-4 w-4" />
                  Professional Biography
                </CardTitle>
              </CardHeader>
              <CardContent className="p-4">
                {isEditing ? (
                  <Textarea
                    id="bio"
                    value={profileData.bio}
                    onChange={(e) => setProfileData({...profileData, bio: e.target.value})}
                    className="min-h-[200px] resize-none"
                    style={{ height: `${Math.max(200, (profileData.bio?.split('\n').length || 1) * 24 + 48)}px` }}
                    placeholder="Share your professional background..."
                  />
                ) : (
                  <div className="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p className="text-sm text-gray-700 whitespace-pre-wrap">
                      {profileData.bio || 'No biography provided.'}
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Links & Social Card */}
            <Card className="shadow-lg rounded-2xl border border-gray-200 h-full">
              <CardHeader className="h-12 flex items-center px-4 rounded-t-lg border-b border-gray-200">
                <CardTitle className="flex items-center gap-2 text-gray-900 text-sm font-semibold">
                  <Globe className="h-4 w-4" />
                  Links & Social
                </CardTitle>
              </CardHeader>
              <CardContent className="p-4 space-y-2">
                <div className="grid grid-cols-2 gap-2">
                  {isEditing ? (
                    <>
                      <FloatingInput
                        id="website"
                        label="Website"
                        type="url"
                        value={profileData.website}
                        onChange={(e) => setProfileData({...profileData, website: e.target.value})}
                      />
                      <FloatingInput
                        id="linkedin"
                        label="LinkedIn"
                        type="url"
                        value={profileData.linkedin}
                        onChange={(e) => setProfileData({...profileData, linkedin: e.target.value})}
                      />
                      <FloatingInput
                        id="facebook"
                        label="Facebook"
                        type="url"
                        value={profileData.facebook}
                        onChange={(e) => setProfileData({...profileData, facebook: e.target.value})}
                      />
                      <FloatingInput
                        id="instagram"
                        label="Instagram"
                        type="url"
                        value={profileData.instagram}
                        onChange={(e) => setProfileData({...profileData, instagram: e.target.value})}
                      />
                    </>
                  ) : (
                    <>
                      <div className="flex items-center gap-2 p-2 bg-gray-50 rounded border">
                        <Globe className="h-4 w-4 text-gray-600" />
                        <span className="text-xs truncate">{profileData.website || 'Website'}</span>
                      </div>
                      <div className="flex items-center gap-2 p-2 bg-gray-50 rounded border">
                        <Linkedin className="h-4 w-4 text-gray-600" />
                        <span className="text-xs truncate">{profileData.linkedin || 'LinkedIn'}</span>
                      </div>
                      <div className="flex items-center gap-2 p-2 bg-gray-50 rounded border">
                        <Facebook className="h-4 w-4 text-gray-600" />
                        <span className="text-xs truncate">{profileData.facebook || 'Facebook'}</span>
                      </div>
                      <div className="flex items-center gap-2 p-2 bg-gray-50 rounded border">
                        <Smartphone className="h-4 w-4 text-gray-600" />
                        <span className="text-xs truncate">{profileData.instagram || 'Instagram'}</span>
                      </div>
                    </>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Specialties */}
            <Card className="shadow-lg rounded-2xl border border-gray-200 h-full overflow-y-auto">
              <CardHeader className="h-12 flex items-center px-4 rounded-t-lg border-b border-gray-200">
                <CardTitle className="flex items-center gap-2 text-gray-900 text-sm font-semibold">
                  <CheckSquare className="h-4 w-4" />
                  Specialties & Credentials
                </CardTitle>
              </CardHeader>
              <CardContent className="p-4 space-y-4">
                {/* Loan Officer Specialties */}
                <div>
                  <Label className="text-xs font-medium mb-2 block">Loan Officer Specialties</Label>
                  {isEditing ? (
                    <div className="space-y-2">
                      {[
                        'Residential Mortgages',
                        'Consumer Loans',
                        'VA Loans',
                        'FHA Loans',
                        'Jumbo Loans',
                        'Construction Loans',
                        'Investment Property',
                        'Reverse Mortgages',
                        'USDA Rural Loans',
                        'Bridge Loans'
                      ].map((specialty) => (
                        <label key={specialty} className="flex items-center space-x-2 cursor-pointer">
                          <input
                            type="checkbox"
                            checked={profileData.specialtiesLo.includes(specialty)}
                            onChange={(e) => {
                              if (e.target.checked) {
                                setProfileData({
                                  ...profileData,
                                  specialtiesLo: [...profileData.specialtiesLo, specialty]
                                });
                              } else {
                                setProfileData({
                                  ...profileData,
                                  specialtiesLo: profileData.specialtiesLo.filter(s => s !== specialty)
                                });
                              }
                            }}
                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                          />
                          <span className="text-sm text-gray-700">{specialty}</span>
                        </label>
                      ))}
                    </div>
                  ) : (
                    <div className="flex flex-wrap gap-2">
                      {profileData.specialtiesLo.length > 0 ? (
                        profileData.specialtiesLo.map((specialty) => (
                          <Badge key={specialty} variant="secondary" className="text-xs">
                            {specialty}
                          </Badge>
                        ))
                      ) : (
                        <p className="text-xs text-gray-500 italic">No specialties selected</p>
                      )}
                    </div>
                  )}
                </div>

                {/* NAMB Certifications */}
                <div>
                  <Label className="text-xs font-medium mb-2 block">NAMB Certifications</Label>
                  {isEditing ? (
                    <div className="space-y-2">
                      {[
                        'CMC - Certified Mortgage Consultant',
                        'CRMS - Certified Residential Mortgage Specialist',
                        'GMA - General Mortgage Associate',
                        'CVLS - Certified Veterans Lending Specialist'
                      ].map((cert) => (
                        <label key={cert} className="flex items-center space-x-2 cursor-pointer">
                          <input
                            type="checkbox"
                            checked={profileData.nambCertifications.includes(cert)}
                            onChange={(e) => {
                              if (e.target.checked) {
                                setProfileData({
                                  ...profileData,
                                  nambCertifications: [...profileData.nambCertifications, cert]
                                });
                              } else {
                                setProfileData({
                                  ...profileData,
                                  nambCertifications: profileData.nambCertifications.filter(c => c !== cert)
                                });
                              }
                            }}
                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                          />
                          <span className="text-sm text-gray-700">{cert}</span>
                        </label>
                      ))}
                    </div>
                  ) : (
                    <div className="flex flex-wrap gap-2">
                      {profileData.nambCertifications.length > 0 ? (
                        profileData.nambCertifications.map((cert) => (
                          <Badge key={cert} variant="secondary" className="text-xs bg-purple-100 text-purple-800">
                            {cert}
                          </Badge>
                        ))
                      ) : (
                        <p className="text-xs text-gray-500 italic">No certifications selected</p>
                      )}
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>


        </div>
      )}

      {/* Settings Tab */}
      {currentTab === 'settings' && (
        <div className="space-y-4">
          <Card className="border-[var(--brand-powder-blue)]">
            <CardHeader>
              <CardTitle className="text-[var(--brand-dark-navy)] flex items-center">
                <Settings className="h-5 w-5 mr-2" />
                Account Settings
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="flex items-center justify-between p-4 border rounded-lg">
                <div className="space-y-1">
                  <h4 className="text-sm font-medium text-[var(--brand-dark-navy)]">Email Notifications</h4>
                  <p className="text-sm text-[var(--brand-slate)]">Receive notifications for new leads and partnerships</p>
                </div>
                <Switch defaultChecked />
              </div>

              <div className="flex items-center justify-between p-4 border rounded-lg">
                <div className="space-y-1">
                  <h4 className="text-sm font-medium text-[var(--brand-dark-navy)]">SMS Notifications</h4>
                  <p className="text-sm text-[var(--brand-slate)]">Get text messages for urgent updates</p>
                </div>
                <Switch />
              </div>

              <div className="flex items-center justify-between p-4 border rounded-lg">
                <div className="space-y-1">
                  <h4 className="text-sm font-medium text-[var(--brand-dark-navy)]">Profile Visibility</h4>
                  <p className="text-sm text-[var(--brand-slate)]">Make your profile visible to potential partners</p>
                </div>
                <Switch defaultChecked />
              </div>
            </CardContent>
          </Card>

          <Card className="border-[var(--brand-powder-blue)]">
            <CardHeader>
              <CardTitle className="text-[var(--brand-dark-navy)] flex items-center">
                <Shield className="h-5 w-5 mr-2" />
                Privacy & Security
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <Button variant="outline" className="w-full justify-start">
                <Bell className="h-4 w-4 mr-2" />
                Change Password
              </Button>

              <Button variant="outline" className="w-full justify-start">
                <FileText className="h-4 w-4 mr-2" />
                Download My Data
              </Button>

              <div className="pt-4 border-t">
                <Button variant="destructive" className="w-full">
                  Delete Account
                </Button>
                <p className="text-xs text-[var(--brand-slate)] mt-2 text-center">
                  This action cannot be undone. All your data will be permanently removed.
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Announcement Modal */}
      {isAnnouncementModalOpen && selectedAnnouncement && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <div className="p-6">
              <div className="flex items-start justify-between mb-4">
                <h2 className="text-xl font-semibold text-[var(--brand-dark-navy)]">
                  {selectedAnnouncement.title}
                </h2>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => setIsAnnouncementModalOpen(false)}
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>

              <div className="space-y-4">
                <p className="text-sm text-[var(--brand-slate)]">
                  {new Date(selectedAnnouncement.date).toLocaleDateString()}
                </p>

                {selectedAnnouncement.thumbnail && (
                  <img
                    src={selectedAnnouncement.thumbnail}
                    alt={selectedAnnouncement.title}
                    className="w-full h-48 object-cover rounded-lg"
                  />
                )}

                <div
                  className="prose max-w-none text-[var(--brand-dark-navy)]"
                  dangerouslySetInnerHTML={{ __html: selectedAnnouncement.content }}
                />
              </div>

              <div className="mt-6 pt-4 border-t flex justify-end">
                <Button
                  onClick={() => setIsAnnouncementModalOpen(false)}
                  className="bg-[var(--brand-electric-blue)] hover:bg-[var(--brand-electric-blue)]/90 text-white"
                >
                  Close
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}