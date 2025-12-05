import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { Calculator, DollarSign, Home, TrendingUp } from 'lucide-react';
import {
  ConventionalCalculator,
  AffordabilityCalculator,
  BuydownCalculator,
  DSCRCalculator,
  RefinanceCalculator,
  NetProceedsCalculator,
  RentVsBuyCalculator
} from '../calculators';

export function CalculatorTools() {
  const [activeTab, setActiveTab] = useState('conventional');
  return (
    <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
      <div className="max-w-7xl mx-auto">
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-2">Calculator & Tools</h1>
          <p className="text-gray-600 text-lg">
            Professional mortgage calculators to help your clients
          </p>
        </div>

        <Card className="border-2 border-black/20">
          <CardHeader className="bg-black/5">
            <CardTitle className="flex items-center gap-2">
              <Calculator className="h-6 w-6 text-black" />
              Mortgage Calculators
            </CardTitle>
          </CardHeader>
          <CardContent className="pt-6">
            <div
              className="w-full"
              style={{
                '--brand-primary-blue': '#000000',
                '--brand-rich-teal': '#000000',
              } as React.CSSProperties}
            >
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
                <TabsList className="grid w-full grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 mb-6 gap-1">
                  <TabsTrigger value="conventional">Payment</TabsTrigger>
                  <TabsTrigger value="affordability">Affordability</TabsTrigger>
                  <TabsTrigger value="buydown">Buydown</TabsTrigger>
                  <TabsTrigger value="dscr">DSCR</TabsTrigger>
                  <TabsTrigger value="refinance">Refinance</TabsTrigger>
                  <TabsTrigger value="netproceeds">Net Proceeds</TabsTrigger>
                  <TabsTrigger value="rentvsbuy">Rent vs Buy</TabsTrigger>
                </TabsList>

                <TabsContent value="conventional">
                  <ConventionalCalculator brandColor="#000000" />
                </TabsContent>

                <TabsContent value="affordability">
                  <AffordabilityCalculator brandColor="#000000" />
                </TabsContent>

                <TabsContent value="buydown">
                  <BuydownCalculator brandColor="#000000" />
                </TabsContent>

                <TabsContent value="dscr">
                  <DSCRCalculator brandColor="#000000" />
                </TabsContent>

                <TabsContent value="refinance">
                  <RefinanceCalculator brandColor="#000000" />
                </TabsContent>

                <TabsContent value="netproceeds">
                  <NetProceedsCalculator brandColor="#000000" />
                </TabsContent>

                <TabsContent value="rentvsbuy">
                  <RentVsBuyCalculator brandColor="#000000" />
                </TabsContent>
              </Tabs>
            </div>

            <p className="text-gray-600 mt-6 text-sm border-t pt-4">
              Use these calculators to help your clients estimate monthly payments, compare loan options,
              understand refinancing benefits, and make informed decisions about their home purchase or refinance.
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
