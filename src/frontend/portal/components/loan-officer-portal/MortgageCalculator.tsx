import { useState, useEffect } from 'react';
import { Card, CardContent } from '../ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { Calculator, User, Mail } from 'lucide-react';
import { PageHeader } from './PageHeader';
import {
  ConventionalCalculator,
  AffordabilityCalculator,
  BuydownCalculator,
  DSCRCalculator,
  RefinanceCalculator,
  NetProceedsCalculator,
  RentVsBuyCalculator
} from '../calculators';

// Loan Officer Profile Component
function LoanOfficerProfile() {
  const [nmls, setNmls] = useState('');
  const userName = (window as any).frsPortalConfig?.userName || '';
  const userEmail = (window as any).frsPortalConfig?.userEmail || '';
  const userAvatar = (window as any).frsPortalConfig?.userAvatar || '';
  const userId = (window as any).frsPortalConfig?.userId || '';

  useEffect(() => {
    // Fetch NMLS from frs-users profile
    if (userId) {
      fetch(`/wp-json/frs-users/v1/profiles/by-user/${userId}`, {
        credentials: 'same-origin',
        headers: {
          'X-WP-Nonce': (window as any).frsPortalConfig?.restNonce || ''
        }
      })
        .then(res => res.json())
        .then(data => {
          if (data && (data.nmls || data.nmls_number)) {
            setNmls(data.nmls || data.nmls_number);
          }
        })
        .catch(err => console.error('Failed to fetch NMLS:', err));
    }
  }, [userId]);

  return (
    <Card className="mb-6">
      <CardContent className="flex items-center gap-4 p-6">
        <div
          className="relative p-1 rounded-full"
          style={{
            background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)'
          }}
        >
          {userAvatar ? (
            <img
              src={userAvatar}
              alt={userName}
              className="w-24 h-24 rounded-full object-cover"
            />
          ) : (
            <div className="w-24 h-24 rounded-full bg-white flex items-center justify-center">
              <User className="w-12 h-12 text-gray-400" />
            </div>
          )}
        </div>
        <div className="flex-1">
          <h3
            className="text-2xl font-bold"
            style={{
              backgroundImage: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              backgroundClip: 'text'
            }}
          >
            {userName}
          </h3>
          {nmls && (
            <p className="text-sm text-muted-foreground mt-1">NMLS# {nmls}</p>
          )}
          {userEmail && (
            <div className="flex items-center gap-1 text-sm text-muted-foreground mt-1">
              <Mail className="w-3 h-3" />
              <span>{userEmail}</span>
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}

export function MortgageCalculator() {
  const [activeTab, setActiveTab] = useState('conventional');

  return (
    <div className="w-full max-w-7xl mx-auto p-6">
      {/* Page Header */}
      <PageHeader
        icon={Calculator}
        title="Mortgage Calculator"
        iconBgColor="linear-gradient(135deg, #3b82f6 0%, #2DD4DA 100%)"
      />
      <p className="text-muted-foreground mt-2 mb-6">
        Calculate payments for different mortgage types
      </p>

      {/* Loan Officer Profile */}
      <LoanOfficerProfile />

      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        {/* Mobile: Dropdown selector */}
        <div className="md:hidden mb-6">
          <Select value={activeTab} onValueChange={setActiveTab}>
            <SelectTrigger className="w-full">
              <SelectValue placeholder="Select calculator type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="conventional">Payment Calculator</SelectItem>
              <SelectItem value="affordability">Affordability Calculator</SelectItem>
              <SelectItem value="buydown">Buydown Calculator</SelectItem>
              <SelectItem value="dscr">DSCR Calculator</SelectItem>
              <SelectItem value="refinance">Refinance Calculator</SelectItem>
              <SelectItem value="netproceeds">Net Proceeds Calculator</SelectItem>
              <SelectItem value="rentvsbuy">Rent vs Buy Calculator</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {/* Desktop: Tabs */}
        <TabsList className="hidden md:grid w-full grid-cols-7 mb-6">
          <TabsTrigger value="conventional">Payment</TabsTrigger>
          <TabsTrigger value="affordability">Affordability</TabsTrigger>
          <TabsTrigger value="buydown">Buydown</TabsTrigger>
          <TabsTrigger value="dscr">DSCR</TabsTrigger>
          <TabsTrigger value="refinance">Refinance</TabsTrigger>
          <TabsTrigger value="netproceeds">Net Proceeds</TabsTrigger>
          <TabsTrigger value="rentvsbuy">Rent vs Buy</TabsTrigger>
        </TabsList>

        <TabsContent value="conventional">
          <ConventionalCalculator />
        </TabsContent>

        <TabsContent value="affordability">
          <AffordabilityCalculator />
        </TabsContent>

        <TabsContent value="buydown">
          <BuydownCalculator />
        </TabsContent>

        <TabsContent value="dscr">
          <DSCRCalculator />
        </TabsContent>

        <TabsContent value="refinance">
          <RefinanceCalculator />
        </TabsContent>

        <TabsContent value="netproceeds">
          <NetProceedsCalculator />
        </TabsContent>

        <TabsContent value="rentvsbuy">
          <RentVsBuyCalculator />
        </TabsContent>
      </Tabs>
    </div>
  );
}
