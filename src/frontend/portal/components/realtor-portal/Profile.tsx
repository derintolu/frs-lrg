import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Textarea } from '../ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../ui/tabs';
import { Badge } from '../ui/badge';
import {
  User,
  Mail,
  Phone,
  MapPin,
  Globe,
  Save,
  Upload,
  Award,
  Briefcase,
  Facebook,
  Instagram,
  Linkedin,
  Twitter,
  Youtube,
  Video,
  Wrench,
  Loader2
} from 'lucide-react';

interface ProfileProps {
  userId: string;
  currentUser: any;
  companyName?: string;
}

interface ProfileData {
  id: number;
  user_id: number;
  frs_agent_id?: string;
  email: string;
  first_name: string;
  last_name: string;
  display_name?: string;
  phone_number?: string;
  mobile_number?: string;
  office?: string;
  headshot_id?: number;
  headshot_url?: string;
  job_title?: string;
  biography?: string;
  date_of_birth?: string;
  select_person_type?: string;
  nmls?: string;
  nmls_number?: string;
  license_number?: string;
  dre_license?: string;
  specialties_lo?: string[];
  specialties?: string[];
  nar_designations?: string[];
  namb_certifications?: string[];
  awards?: Array<{
    year: string;
    award: string;
    award_logo?: string;
  }>;
  languages?: string[];
  brand?: string;
  status?: string;
  city_state?: string;
  region?: string;
  facebook_url?: string;
  instagram_url?: string;
  linkedin_url?: string;
  twitter_url?: string;
  youtube_url?: string;
  tiktok_url?: string;
  arrive?: string;
  canva_folder_link?: string;
  niche_bio_content?: Array<{
    title: string;
    bio_content: string;
  }>;
  personal_branding_images?: number[];
  loan_officer_profile?: number;
  loan_officer_user?: number;
}

const REALTOR_SPECIALTIES = [
  'Residential',
  'Commercial',
  'Luxury',
  'Investment',
  'New Construction',
  'Condos/Townhouses',
  'Multi-Family',
  'Land Sales',
  'Vacation Homes',
  'First-Time Buyers',
  'Military/Veterans',
  'Seniors',
  'International',
  'Relocation',
  'Foreclosures',
  'Green/Eco',
  'Historic Properties',
  'Waterfront',
  'Rural',
  'Urban',
  'Distressed Sales',
  'Fix-and-Flip',
];

const NAR_DESIGNATIONS = [
  "ABR - Accredited Buyer's Representative",
  'CRS - Certified Residential Specialist',
  'SRES - Seniors Real Estate Specialist',
  'SRS - Seller Representative Specialist',
  'GRI - Graduate REALTOR® Institute',
  'CRB - Certified Real Estate Brokerage Manager',
  'CCIM - Certified Commercial Investment Member',
  'CIPS - Certified International Property Specialist',
  'CPM - Certified Property Manager',
  'CRE - Counselor of Real Estate',
  'ALC - Accredited Land Consultant',
  'MRP - Military Relocation Professional',
  'RSPS - Resort & Second-Home Property Specialist',
];

const CENTURY21_AWARDS = [
  'GRAND CENTURION®',
  'Double CENTURION®',
  'CENTURION®',
  'CENTURION® Team',
  'CENTURION® Honor Society',
  'Masters Diamond',
  'Masters Emerald',
  'Masters Ruby',
  'Masters Team',
  'Quality Service Pinnacle Producer',
  'Quality Service Producer',
  "President's Producer",
  'Agent of the Year',
];

const LANGUAGES = [
  'English',
  'Spanish',
  'Mandarin',
  'Cantonese',
  'Turkish',
  'French',
  'German',
  'Russian',
  'Arabic',
];

export function Profile({ userId, currentUser, companyName }: ProfileProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [profileData, setProfileData] = useState<ProfileData | null>(null);
  const [originalData, setOriginalData] = useState<ProfileData | null>(null);

  // Load profile data from API
  useEffect(() => {
    const loadProfile = async () => {
      try {
        setIsLoading(true);
        const response = await fetch('/wp-json/frs-users/v1/profiles/user/me', {
          credentials: 'include',
        });

        if (!response.ok) {
          throw new Error('Failed to load profile');
        }

        const result = await response.json();
        if (result.success && result.data) {
          setProfileData(result.data);
          setOriginalData(result.data);
        }
      } catch (error) {
        console.error('Failed to load profile:', error);
        alert('Failed to load profile. Please try again.');
      } finally {
        setIsLoading(false);
      }
    };

    loadProfile();
  }, [userId]);

  const handleSave = async () => {
    if (!profileData) return;

    setIsSaving(true);
    try {
      const response = await fetch(`/wp-json/frs-users/v1/profiles/${profileData.id}`, {
        method: 'PUT',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(profileData),
      });

      if (!response.ok) {
        throw new Error('Failed to save profile');
      }

      const result = await response.json();
      if (result.success && result.data) {
        setProfileData(result.data);
        setOriginalData(result.data);
        setIsEditing(false);
        alert('Profile updated successfully!');
      }
    } catch (error) {
      console.error('Failed to save profile:', error);
      alert('Failed to save profile. Please try again.');
    } finally {
      setIsSaving(false);
    }
  };

  const handleCancel = () => {
    setIsEditing(false);
    setProfileData(originalData);
  };

  const toggleSpecialty = (specialty: string) => {
    if (!profileData) return;
    const specialties = profileData.specialties || [];
    const newSpecialties = specialties.includes(specialty)
      ? specialties.filter(s => s !== specialty)
      : [...specialties, specialty];
    setProfileData({ ...profileData, specialties: newSpecialties });
  };

  const toggleDesignation = (designation: string) => {
    if (!profileData) return;
    const designations = profileData.nar_designations || [];
    const newDesignations = designations.includes(designation)
      ? designations.filter(d => d !== designation)
      : [...designations, designation];
    setProfileData({ ...profileData, nar_designations: newDesignations });
  };

  const toggleLanguage = (language: string) => {
    if (!profileData) return;
    const languages = profileData.languages || [];
    const newLanguages = languages.includes(language)
      ? languages.filter(l => l !== language)
      : [...languages, language];
    setProfileData({ ...profileData, languages: newLanguages });
  };

  const addAward = (year: string, award: string) => {
    if (!profileData || !year || !award) return;
    const awards = profileData.awards || [];
    const newAward = { year, award, award_logo: '' };
    setProfileData({ ...profileData, awards: [...awards, newAward] });
  };

  const removeAward = (index: number) => {
    if (!profileData) return;
    const awards = profileData.awards || [];
    const newAwards = awards.filter((_, i) => i !== index);
    setProfileData({ ...profileData, awards: newAwards });
  };

  if (isLoading) {
    return (
      <div className="w-full min-h-screen flex items-center justify-center bg-gray-50/50">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin text-gray-600 mx-auto mb-2" />
          <p className="text-gray-600">Loading profile...</p>
        </div>
      </div>
    );
  }

  if (!profileData) {
    return (
      <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
        <div className="max-w-5xl mx-auto">
          <Card>
            <CardContent className="p-6">
              <p className="text-gray-600">Profile not found.</p>
            </CardContent>
          </Card>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
      <div className="max-w-5xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-start">
            <div>
              <h1 className="text-4xl font-bold text-gray-900 mb-2">My Profile</h1>
              <p className="text-gray-600 text-lg">
                Manage your personal information and professional details
              </p>
            </div>
            {!isEditing ? (
              <Button
                onClick={() => setIsEditing(true)}
                className="bg-black hover:bg-black/90 text-white"
              >
                Edit Profile
              </Button>
            ) : (
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  onClick={handleCancel}
                  disabled={isSaving}
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleSave}
                  disabled={isSaving}
                  className="bg-black hover:bg-black/90 text-white"
                >
                  {isSaving ? (
                    <>
                      <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                      Saving...
                    </>
                  ) : (
                    <>
                      <Save className="h-4 w-4 mr-2" />
                      Save Changes
                    </>
                  )}
                </Button>
              </div>
            )}
          </div>
        </div>

        <Tabs defaultValue="contact" className="space-y-6">
          <TabsList className="grid w-full grid-cols-5">
            <TabsTrigger value="contact">Contact</TabsTrigger>
            <TabsTrigger value="professional">Professional</TabsTrigger>
            <TabsTrigger value="location">Location</TabsTrigger>
            <TabsTrigger value="social">Social Media</TabsTrigger>
            <TabsTrigger value="tools">Tools</TabsTrigger>
          </TabsList>

          {/* Contact Tab */}
          <TabsContent value="contact" className="space-y-6">
            {/* Profile Photo */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="h-5 w-5" />
                  Profile Photo
                </CardTitle>
                <CardDescription>
                  Upload a professional headshot for your co-branded marketing materials
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-6">
                  {profileData.headshot_url ? (
                    <img
                      src={profileData.headshot_url}
                      alt="Profile Photo"
                      className="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
                    />
                  ) : (
                    <div className="w-24 h-24 rounded-full bg-gray-100 flex items-center justify-center border-2 border-gray-200">
                      <User className="h-10 w-10 text-gray-400" />
                    </div>
                  )}
                  {isEditing && (
                    <Button variant="outline">
                      <Upload className="h-4 w-4 mr-2" />
                      Upload Photo
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Contact Information */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Mail className="h-5 w-5" />
                  Contact Information
                </CardTitle>
                <CardDescription>
                  Your basic contact details
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="first_name">First Name *</Label>
                    <Input
                      id="first_name"
                      value={profileData.first_name}
                      onChange={(e) => setProfileData({ ...profileData, first_name: e.target.value })}
                      disabled={!isEditing}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="last_name">Last Name *</Label>
                    <Input
                      id="last_name"
                      value={profileData.last_name}
                      onChange={(e) => setProfileData({ ...profileData, last_name: e.target.value })}
                      disabled={!isEditing}
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="email">Email Address *</Label>
                    <div className="relative">
                      <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                      <Input
                        id="email"
                        type="email"
                        value={profileData.email}
                        onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
                        disabled={!isEditing}
                        className="pl-10"
                      />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="phone_number">Phone Number</Label>
                    <div className="relative">
                      <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                      <Input
                        id="phone_number"
                        type="tel"
                        placeholder="(555) 555-5555"
                        value={profileData.phone_number || ''}
                        onChange={(e) => setProfileData({ ...profileData, phone_number: e.target.value })}
                        disabled={!isEditing}
                        className="pl-10"
                      />
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="mobile_number">Mobile Number</Label>
                    <div className="relative">
                      <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                      <Input
                        id="mobile_number"
                        type="tel"
                        placeholder="(555) 555-5555"
                        value={profileData.mobile_number || ''}
                        onChange={(e) => setProfileData({ ...profileData, mobile_number: e.target.value })}
                        disabled={!isEditing}
                        className="pl-10"
                      />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="office">Office</Label>
                    <Input
                      id="office"
                      placeholder="Office location or name"
                      value={profileData.office || ''}
                      onChange={(e) => setProfileData({ ...profileData, office: e.target.value })}
                      disabled={!isEditing}
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Professional Tab */}
          <TabsContent value="professional" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Briefcase className="h-5 w-5" />
                  Professional Information
                </CardTitle>
                <CardDescription>
                  Your professional credentials and experience
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="job_title">Job Title</Label>
                  <Input
                    id="job_title"
                    placeholder="e.g., Real Estate Agent"
                    value={profileData.job_title || ''}
                    onChange={(e) => setProfileData({ ...profileData, job_title: e.target.value })}
                    disabled={!isEditing}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="biography">Professional Bio</Label>
                  <Textarea
                    id="biography"
                    placeholder="Tell clients about yourself and your approach to real estate..."
                    value={profileData.biography || ''}
                    onChange={(e) => setProfileData({ ...profileData, biography: e.target.value })}
                    disabled={!isEditing}
                    rows={6}
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="license_number">Real Estate License #</Label>
                    <Input
                      id="license_number"
                      placeholder="License number"
                      value={profileData.license_number || ''}
                      onChange={(e) => setProfileData({ ...profileData, license_number: e.target.value })}
                      disabled={!isEditing}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="dre_license">DRE License (CA)</Label>
                    <Input
                      id="dre_license"
                      placeholder="California DRE license"
                      value={profileData.dre_license || ''}
                      onChange={(e) => setProfileData({ ...profileData, dre_license: e.target.value })}
                      disabled={!isEditing}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="brand">Brand</Label>
                    <Input
                      id="brand"
                      placeholder="Professional brand name"
                      value={profileData.brand || ''}
                      onChange={(e) => setProfileData({ ...profileData, brand: e.target.value })}
                      disabled={!isEditing}
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>Specialties</Label>
                  <CardDescription className="mb-2">
                    Select all areas of real estate you specialize in
                  </CardDescription>
                  <div className="flex flex-wrap gap-2">
                    {REALTOR_SPECIALTIES.map((specialty) => (
                      <Badge
                        key={specialty}
                        variant={profileData.specialties?.includes(specialty) ? 'default' : 'outline'}
                        className={`cursor-pointer ${
                          isEditing ? 'hover:bg-gray-200' : 'pointer-events-none'
                        } ${profileData.specialties?.includes(specialty) ? 'bg-black text-white' : ''}`}
                        onClick={() => isEditing && toggleSpecialty(specialty)}
                      >
                        {specialty}
                      </Badge>
                    ))}
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>NAR Designations</Label>
                  <CardDescription className="mb-2">
                    National Association of Realtors professional designations
                  </CardDescription>
                  <div className="flex flex-wrap gap-2">
                    {NAR_DESIGNATIONS.map((designation) => (
                      <Badge
                        key={designation}
                        variant={profileData.nar_designations?.includes(designation) ? 'default' : 'outline'}
                        className={`cursor-pointer ${
                          isEditing ? 'hover:bg-gray-200' : 'pointer-events-none'
                        } ${profileData.nar_designations?.includes(designation) ? 'bg-black text-white' : ''}`}
                        onClick={() => isEditing && toggleDesignation(designation)}
                      >
                        {designation}
                      </Badge>
                    ))}
                  </div>
                </div>

                <div className="space-y-2">
                  <Label>Languages</Label>
                  <CardDescription className="mb-2">
                    Languages you speak with clients
                  </CardDescription>
                  <div className="flex flex-wrap gap-2">
                    {LANGUAGES.map((language) => (
                      <Badge
                        key={language}
                        variant={profileData.languages?.includes(language) ? 'default' : 'outline'}
                        className={`cursor-pointer ${
                          isEditing ? 'hover:bg-gray-200' : 'pointer-events-none'
                        } ${profileData.languages?.includes(language) ? 'bg-black text-white' : ''}`}
                        onClick={() => isEditing && toggleLanguage(language)}
                      >
                        {language}
                      </Badge>
                    ))}
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Location Tab */}
          <TabsContent value="location" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <MapPin className="h-5 w-5" />
                  Service Location
                </CardTitle>
                <CardDescription>
                  Areas where you provide real estate services
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="city_state">City, State</Label>
                  <Input
                    id="city_state"
                    placeholder="e.g., Bloomfield Hills, MI"
                    value={profileData.city_state || ''}
                    onChange={(e) => setProfileData({ ...profileData, city_state: e.target.value })}
                    disabled={!isEditing}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="region">Region</Label>
                  <Input
                    id="region"
                    placeholder="e.g., Metro Detroit"
                    value={profileData.region || ''}
                    onChange={(e) => setProfileData({ ...profileData, region: e.target.value })}
                    disabled={!isEditing}
                  />
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Social Media Tab */}
          <TabsContent value="social" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Globe className="h-5 w-5" />
                  Social Media Links
                </CardTitle>
                <CardDescription>
                  Connect your social media profiles
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="facebook_url">Facebook</Label>
                  <div className="relative">
                    <Facebook className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="facebook_url"
                      placeholder="https://facebook.com/yourprofile"
                      value={profileData.facebook_url || ''}
                      onChange={(e) => setProfileData({ ...profileData, facebook_url: e.target.value })}
                      disabled={!isEditing}
                      className="pl-10"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="instagram_url">Instagram</Label>
                  <div className="relative">
                    <Instagram className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="instagram_url"
                      placeholder="https://instagram.com/yourprofile"
                      value={profileData.instagram_url || ''}
                      onChange={(e) => setProfileData({ ...profileData, instagram_url: e.target.value })}
                      disabled={!isEditing}
                      className="pl-10"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="linkedin_url">LinkedIn</Label>
                  <div className="relative">
                    <Linkedin className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="linkedin_url"
                      placeholder="https://linkedin.com/in/yourprofile"
                      value={profileData.linkedin_url || ''}
                      onChange={(e) => setProfileData({ ...profileData, linkedin_url: e.target.value })}
                      disabled={!isEditing}
                      className="pl-10"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="twitter_url">Twitter / X</Label>
                  <div className="relative">
                    <Twitter className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="twitter_url"
                      placeholder="https://twitter.com/yourprofile"
                      value={profileData.twitter_url || ''}
                      onChange={(e) => setProfileData({ ...profileData, twitter_url: e.target.value })}
                      disabled={!isEditing}
                      className="pl-10"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="youtube_url">YouTube</Label>
                  <div className="relative">
                    <Youtube className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="youtube_url"
                      placeholder="https://youtube.com/@yourprofile"
                      value={profileData.youtube_url || ''}
                      onChange={(e) => setProfileData({ ...profileData, youtube_url: e.target.value })}
                      disabled={!isEditing}
                      className="pl-10"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="tiktok_url">TikTok</Label>
                  <div className="relative">
                    <Video className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="tiktok_url"
                      placeholder="https://tiktok.com/@yourprofile"
                      value={profileData.tiktok_url || ''}
                      onChange={(e) => setProfileData({ ...profileData, tiktok_url: e.target.value })}
                      disabled={!isEditing}
                      className="pl-10"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Tools Tab */}
          <TabsContent value="tools" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Wrench className="h-5 w-5" />
                  Tools & Platforms
                </CardTitle>
                <CardDescription>
                  Integration with professional tools and platforms
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="arrive">ARRIVE</Label>
                  <Input
                    id="arrive"
                    placeholder="ARRIVE platform URL"
                    value={profileData.arrive || ''}
                    onChange={(e) => setProfileData({ ...profileData, arrive: e.target.value })}
                    disabled={!isEditing}
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="canva_folder_link">Canva Folder Link</Label>
                  <Input
                    id="canva_folder_link"
                    placeholder="Link to your Canva marketing materials"
                    value={profileData.canva_folder_link || ''}
                    onChange={(e) => setProfileData({ ...profileData, canva_folder_link: e.target.value })}
                    disabled={!isEditing}
                  />
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
