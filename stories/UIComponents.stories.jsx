import React from 'react';
import '../src/frontend/index.css';
import { Button } from '../src/frontend/portal/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '../src/frontend/portal/components/ui/card';
import { Input } from '../src/frontend/portal/components/ui/input';
import { Label } from '../src/frontend/portal/components/ui/label';
import { Badge } from '../src/frontend/portal/components/ui/badge';
import { Alert, AlertDescription, AlertTitle } from '../src/frontend/portal/components/ui/alert';

export default {
  title: 'UI Components/Basics',
  parameters: {
    layout: 'padded',
  },
};

export const Buttons = () => (
  <div className="space-y-4">
    <div>
      <h3 className="text-lg font-semibold mb-3">Button Variants</h3>
      <div className="flex flex-wrap gap-3">
        <Button variant="default">Default</Button>
        <Button variant="destructive">Destructive</Button>
        <Button variant="outline">Outline</Button>
        <Button variant="secondary">Secondary</Button>
        <Button variant="ghost">Ghost</Button>
        <Button variant="link">Link</Button>
      </div>
    </div>

    <div>
      <h3 className="text-lg font-semibold mb-3">Button Sizes</h3>
      <div className="flex flex-wrap items-center gap-3">
        <Button size="sm">Small</Button>
        <Button size="default">Default</Button>
        <Button size="lg">Large</Button>
        <Button size="icon">🔥</Button>
      </div>
    </div>

    <div>
      <h3 className="text-lg font-semibold mb-3">Button States</h3>
      <div className="flex flex-wrap gap-3">
        <Button>Normal</Button>
        <Button disabled>Disabled</Button>
      </div>
    </div>
  </div>
);

export const Cards = () => (
  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <Card>
      <CardHeader>
        <CardTitle>Simple Card</CardTitle>
        <CardDescription>A basic card with header and content</CardDescription>
      </CardHeader>
      <CardContent>
        <p>This is the card content area.</p>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Card with Footer</CardTitle>
        <CardDescription>Card with actions in footer</CardDescription>
      </CardHeader>
      <CardContent>
        <p>Card content goes here.</p>
      </CardContent>
      <CardFooter className="flex gap-2">
        <Button variant="outline">Cancel</Button>
        <Button>Save</Button>
      </CardFooter>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Full Example</CardTitle>
        <CardDescription>Complete card example</CardDescription>
      </CardHeader>
      <CardContent className="space-y-2">
        <div>
          <Label htmlFor="name">Name</Label>
          <Input id="name" placeholder="Enter your name" />
        </div>
        <div>
          <Label htmlFor="email">Email</Label>
          <Input id="email" type="email" placeholder="Enter your email" />
        </div>
      </CardContent>
      <CardFooter>
        <Button className="w-full">Submit</Button>
      </CardFooter>
    </Card>
  </div>
);

export const Inputs = () => (
  <div className="space-y-4 max-w-md">
    <div>
      <h3 className="text-lg font-semibold mb-3">Form Inputs</h3>
      <div className="space-y-3">
        <div>
          <Label htmlFor="text-input">Text Input</Label>
          <Input id="text-input" placeholder="Enter text..." />
        </div>
        <div>
          <Label htmlFor="email-input">Email Input</Label>
          <Input id="email-input" type="email" placeholder="email@example.com" />
        </div>
        <div>
          <Label htmlFor="password-input">Password Input</Label>
          <Input id="password-input" type="password" placeholder="••••••••" />
        </div>
        <div>
          <Label htmlFor="disabled-input">Disabled Input</Label>
          <Input id="disabled-input" disabled value="Disabled" />
        </div>
      </div>
    </div>
  </div>
);

export const Badges = () => (
  <div className="space-y-4">
    <div>
      <h3 className="text-lg font-semibold mb-3">Badge Variants</h3>
      <div className="flex flex-wrap gap-2">
        <Badge>Default</Badge>
        <Badge variant="secondary">Secondary</Badge>
        <Badge variant="destructive">Destructive</Badge>
        <Badge variant="outline">Outline</Badge>
      </div>
    </div>

    <div>
      <h3 className="text-lg font-semibold mb-3">Badge Examples</h3>
      <div className="flex flex-wrap gap-2">
        <Badge>New</Badge>
        <Badge variant="secondary">In Progress</Badge>
        <Badge variant="destructive">Urgent</Badge>
        <Badge variant="outline">Draft</Badge>
      </div>
    </div>
  </div>
);

export const Alerts = () => (
  <div className="space-y-4">
    <Alert>
      <AlertTitle>Default Alert</AlertTitle>
      <AlertDescription>
        This is a default alert with some important information.
      </AlertDescription>
    </Alert>

    <Alert variant="destructive">
      <AlertTitle>Error Alert</AlertTitle>
      <AlertDescription>
        Something went wrong. Please try again.
      </AlertDescription>
    </Alert>
  </div>
);
