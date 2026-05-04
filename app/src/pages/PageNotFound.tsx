import React from "react";
import { Link } from "react-router-dom";
import { ShieldAlert, LayoutDashboard, LogIn, Lock } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

const PageNotFound = () => {
  return (
    <div className="min-h-screen w-full flex items-center justify-center bg-slate-100 font-sans border-t-4 border-primary">
      {/* Background Decorative Elements for System feel */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none opacity-5">
        <div className="absolute top-10 left-10"><Lock size={200} /></div>
        <div className="absolute bottom-10 right-10"><ShieldAlert size={200} /></div>
      </div>

      <div className="max-w-xl w-full p-4 relative z-10">
        <Card className="border-slate-200 shadow-xl overflow-hidden bg-white">
          <div className="grid md:grid-cols-5 h-full">

            {/* Left Sidebar Accent */}
            <div className="hidden md:flex md:col-span-1 bg-slate-900 items-center justify-center">
              <ShieldAlert className="text-white h-12 w-12 animate-pulse" />
            </div>

            {/* Main Content Area */}
            <div className="md:col-span-4 p-4">
              <CardHeader className="space-y-1">
                <div className="flex items-center gap-2 mb-2">
                  <span className="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded uppercase">
                    System Error 404
                  </span>
                </div>
                <CardTitle className="text-3xl font-bold text-slate-900">
                  Page Not Found
                </CardTitle>
                <CardDescription className="text-slate-500 font-medium">
                  The requested endpoint or path is not available in the current scope.
                </CardDescription>
              </CardHeader>

              <CardContent className="space-y-4">
                <div className="bg-slate-50 border border-slate-200 rounded-lg p-4 font-mono text-sm text-slate-600">
                  <p className="flex justify-between">
                    <span>Attempted URL:</span>
                    <span className="text-red-500">{window.location.pathname}</span>
                  </p>
                  <p className="flex justify-between mt-1">
                    <span>Access Level:</span>
                    <span className="text-slate-900 font-bold">GUEST/UNAUTHORIZED</span>
                  </p>
                </div>
                <p className="text-sm text-slate-500 italic">
                  This incident has been logged for system auditing. Please return to the secure dashboard or login page.
                </p>
              </CardContent>

              <CardFooter className="gap-3">
                <Button asChild className="flex-1 shadow-sm">
                  <Link to="/home">
                    <LayoutDashboard className="h-4 w-4" />
                    Back to Home
                  </Link>
                </Button>

              </CardFooter>
            </div>
          </div>
        </Card>

        <div className="mt-6 flex justify-between items-center text-xs text-slate-400 px-2 uppercase tracking-widest font-semibold">
          <span>User Management System</span>
          <span>Network Services</span>
        </div>
      </div>
    </div>
  );
};

export default PageNotFound;