import React from "react";
import ReactDOM from "react-dom/client";
import "./index.css";
import { RouterProvider } from "react-router-dom";
import { router } from "./routes";
import { ThemeProvider } from "@/components/theme-provider"
const el = document.getElementById("lrh-admin-root");

if (el) {
  ReactDOM.createRoot(el).render(
    <ThemeProvider defaultTheme="light" storageKey="lrh-admin-theme">
    <React.StrictMode>
      <RouterProvider router={router} />
    </React.StrictMode></ThemeProvider>,
  );
}
