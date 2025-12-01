/**
 * Standalone Bento Grid Content
 *
 * This is a router-independent version of the bento grid
 * for use in WordPress blocks without navigation dependencies.
 */

import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../../frontend/portal/components/ui/card';
import { Badge } from '../../frontend/portal/components/ui/badge';
import {
  Bell,
  TrendingUp,
  Calendar,
  Users,
  ArrowRight,
} from 'lucide-react';

interface BentoContentProps {
  userId: string;
}

export function BentoContent({ userId }: BentoContentProps) {
  const [profileData, setProfileData] = useState({
    firstName: '',
    lastName: '',
  });

  useEffect(() => {
    // Fetch user profile data
    const fetchProfile = async () => {
      try {
        const response = await fetch(`/wp-json/wp/v2/users/${userId}`);
        if (response.ok) {
          const data = await response.json();
          setProfileData({
            firstName: data.first_name || '',
            lastName: data.last_name || '',
          });
        }
      } catch (error) {
        console.error('Error fetching profile:', error);
      }
    };

    if (userId) {
      fetchProfile();
    }
  }, [userId]);

  const firstName = profileData.firstName || 'there';

  return (
    <div className="w-full p-4 md:p-8">
      {/* Welcome Header */}
      <div className="mb-8">
        <h1 className="text-3xl md:text-4xl font-bold mb-2">
          Welcome back, {firstName}!
        </h1>
        <p className="text-gray-600">
          Here's what's happening with your portal today.
        </p>
      </div>

      {/* Bento Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* Stats Card */}
        <Card className="col-span-1">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="h-5 w-5" />
              Active Leads
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">0</div>
            <p className="text-sm text-gray-600">No active leads</p>
          </CardContent>
        </Card>

        {/* Partnerships Card */}
        <Card className="col-span-1">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="h-5 w-5" />
              Partnerships
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">0</div>
            <p className="text-sm text-gray-600">No partnerships</p>
          </CardContent>
        </Card>

        {/* Calendar Card */}
        <Card className="col-span-1">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Upcoming Events
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-gray-600">No upcoming events</p>
          </CardContent>
        </Card>

        {/* Announcements */}
        <Card className="col-span-1 md:col-span-2">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Bell className="h-5 w-5" />
              Announcements
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-gray-600">No new announcements</p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
