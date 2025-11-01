import { useState, useEffect } from 'react';
import { DataService, type User } from './utils/dataService';
import { Portal } from './components/loan-officer-portal/Portal';

interface LoanOfficerPortalConfig {
  userId: number;
  userName: string;
  userEmail: string;
  userAvatar: string;
  restNonce: string;
}

export default function LoanOfficerPortal(config: LoanOfficerPortalConfig) {
  const [currentUser, setCurrentUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadUser = async () => {
      try {
        const user = await DataService.getCurrentUser();
        console.log('Loaded user from DataService:', user);
        setCurrentUser(user);
      } catch (err) {
        setError('Failed to load user data');
        console.error('Failed to load user:', err);
      } finally {
        setIsLoading(false);
      }
    };

    loadUser();
  }, []);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-500">Loading portal...</p>
        </div>
      </div>
    );
  }

  if (error || !currentUser) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Error Loading Portal</h2>
          <p className="text-gray-600 mb-4">{error || 'Unable to load user data'}</p>
          <button
            onClick={() => window.location.reload()}
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Retry
          </button>
        </div>
      </div>
    );
  }

  // Use the new Portal with sidebar layout
  return (
    <Portal
      userId={currentUser.id}
      currentUser={currentUser}
    />
  );
}
