import { Link } from "react-router-dom";
import { House } from "lucide-react";

export function Navbar({ email }: { email: string }) {
    return (
        <nav className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur">
            <div className="container flex h-16 items-center justify-between px-4">

                {/* 1. Left Side: Brand/Logo */}
                <div className="flex items-center gap-2">
                    <Link to="/" className="flex items-center gap-2">
                        <House className="h-6 w-6 text-blue-600" />
                        <span className="font-bold text-xl tracking-tight hidden sm:inline-block">
                            User Mangement <span className="text-blue-600">System</span>
                        </span>
                    </Link>
                </div>

                {/* 2. Middle: Navigation Links (Desktop Only) */}
                <div className="hidden md:flex items-center gap-6 text-sm font-medium">
                    Email : {email}
                </div>

                {/* 3. Right Side: Actions & Profile */}
                <div className="flex items-center gap-2">

                </div>
            </div>
        </nav>
    );
}