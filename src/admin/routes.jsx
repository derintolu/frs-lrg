import { createHashRouter } from "react-router-dom";
import ApplicationLayout from "../components/application-layout/LayoutOne";
import Settings from "./pages/settings";
import ErrorPage from "./pages/error/Error";
import Dashboard from "./pages/dashboard";
import Partnerships from "./pages/partnerships";
import BulkInvites from "./pages/bulk-invites";
import Leads from "./pages/leads";
import Integrations from "./pages/integrations";

export const router = createHashRouter([
  {
    path: "/",
    element: <ApplicationLayout />,
    errorElement: <ErrorPage />,
    children: [
      {
        path: "/",
        element: <Dashboard />,
      },
      {
        path: "dashboard",
        element: <Dashboard />,
      },
      {
        path: "partnerships",
        element: <Partnerships />,
      },
      {
        path: "bulk-invites",
        element: <BulkInvites />,
      },
      {
        path: "leads",
        element: <Leads />,
      },
      {
        path: "integrations",
        element: <Integrations />,
      },
      {
        path: "settings",
        element: <Settings />,
      }
    ],
  },
]);
