import { useNavigate } from 'react-router-dom';
import { useEffect } from 'react';
import { useUserAction } from '@/helper/useUserAction';

import {
    Settings,
    LayoutDashboard,
    LogOut,
    ShieldCheck,
    Key,
    Mail
} from 'lucide-react';
// Assuming you have Shadcn components installed in your /components/ui folder
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from "@/components/ui/dropdown-menu";
import { useAuth } from '@/auth/AuthContext';

const HomePage = () => {
    const { fetchUser } = useUserAction();
    const navigate = useNavigate();
    const { handleLogout, user } = useAuth();

    useEffect(() => {
        fetchUser();
    }, []);

    return (
        <div className="min-h-screen bg-slate-50">
            {/* --- Navbar Section --- */}
            <nav className="flex items-center justify-between px-8 py-4 bg-white border-b shadow-sm">
                <div className="flex items-center gap-2">
                    <div className="bg-primary p-2 rounded-lg">
                        <ShieldCheck className="text-white w-6 h-6" />
                    </div>
                    <span className="text-xl font-bold tracking-tight">User Management System</span>
                </div>

                <div className="flex items-center gap-4">
                    {/* Conditional Admin Button */}
                    {user?.role === "admin" && (
                        <Button variant="outline" className="hidden md:flex gap-2" onClick={() => navigate('/admin/dashboard')}>
                            <LayoutDashboard className="w-4 h-4" />
                            Admin Dashboard
                        </Button>
                    )}

                    {/* User Settings Dropdown */}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="relative h-10 w-10 rounded-full">
                                <Avatar>
                                    <AvatarFallback className="bg-primary text-white">
                                        {user?.username.substring(0, 2).toUpperCase()}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-56">
                            <DropdownMenuLabel>
                                <div className="flex flex-col space-y-1">
                                    <p className="text-sm font-medium leading-none">{user?.username}</p>
                                    <p className="text-xs leading-none text-muted-foreground">{user?.email}</p>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem onClick={() => navigate('/user/setting/profile')}>
                                <Settings className="mr-2 h-4 w-4" />
                                <span>Profile Settings</span>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem className="text-red-600" onClick={handleLogout} >
                                <LogOut className="mr-2 h-4 w-4" />
                                <span>Log out</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </nav>

            {/* --- Main Content Section --- */}
            <main className="max-w-5xl mx-auto py-12 px-6">
                <header className="mb-12 text-center">
                    <h1 className="text-4xl font-extrabold text-slate-900 mb-4">
                        Advanced Authentication System
                    </h1>
                    <p className="text-lg text-slate-600 max-w-2xl mx-auto">
                        A secure ecosystem built with React and PHP, focusing on data integrity
                        and modern session management.
                    </p>
                </header>

                <div className="grid md:grid-cols-2 gap-8">
                    {/* Card 1: Token Auth */}
                    <section className="bg-white p-8 rounded-xl border shadow-sm hover:shadow-md transition-shadow">
                        <div className="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                            <Key className="text-blue-600" />
                        </div>
                        <h2 className="text-2xl font-bold mb-4">JWT Session Management</h2>
                        <p className="text-slate-600 leading-relaxed">
                            This project implements a <strong>Dual-Token strategy</strong>. Short-lived
                            <strong> JWT Access Tokens</strong> are stored in application RAM for secure
                            requests, while long-lived <strong>Refresh Tokens</strong> handle persistent
                            sessions via HttpOnly cookies.
                        </p>
                    </section>

                    {/* Card 2: Hashing & Verification */}
                    <section className="bg-white p-8 rounded-xl border shadow-sm hover:shadow-md transition-shadow">
                        <div className="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                            <Mail className="text-green-600" />
                        </div>
                        <h2 className="text-2xl font-bold mb-4">Email & Password Security</h2>
                        <p className="text-slate-600 leading-relaxed">
                            For high-stakes actions like <strong>Email Verification</strong> and
                            <strong> Password Resets</strong>, the system generates unique
                            <strong> SHA-256 hashed tokens</strong>. These are stored in the MySQL
                            database and matched against secure links sent to users.
                        </p>
                    </section>
                </div>

                {/* User Info Badge */}
                <div className="mt-12 p-4 bg-slate-100 rounded-lg inline-flex items-center gap-3">
                    <div className="px-3 py-1 bg-white rounded border text-xs font-mono font-bold uppercase text-slate-500">
                        Current Role: {user?.role}
                    </div>
                    <span className="text-sm text-slate-500 italic">Logged in as {user?.username}</span>
                </div>
            </main>
        </div>
    );
};

export default HomePage;
