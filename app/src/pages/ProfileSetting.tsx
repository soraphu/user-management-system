import { Link } from "react-router-dom"

import { User, ShieldCheck, Mail } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Badge } from "@/components/ui/badge"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"

export default function UserProfileSettings() {
    const user = {
        username: "Soraphu_Dev",
        email: "soraphu_th67@live.rmutl.ac.th",
        role: "user"
    }

    const avatarInitials = user.username.substring(0, 2).toUpperCase();

    return (
        <div className="flex items-center justify-center min-h-screen bg-slate-50 p-4">
            <Card className="w-full max-w-md shadow-lg border-slate-200">
                <CardHeader className="space-y-1 flex flex-col items-center">
                    {/* Mock User Icon Style */}
                    <Avatar className="h-24 w-24 border-2 border-primary">
                        <AvatarFallback className="text-2xl font-bold bg-primary text-primary-foreground">
                            {avatarInitials}
                        </AvatarFallback>
                    </Avatar>
                    <CardTitle className="text-2xl font-bold">Account Settings</CardTitle>
                    <CardDescription>
                        Manage your personal information.
                    </CardDescription>
                </CardHeader>

                <CardContent className="space-y-6">
                    {/* Specific Role Badge */}
                    <div className="flex items-center justify-between p-3 bg-slate-100 rounded-lg">
                        <div className="flex items-center gap-2">
                            <ShieldCheck className="text-primary h-5 w-5" />
                            <span className="text-sm font-medium text-slate-700">Account Role</span>
                        </div>
                        <Badge variant="secondary" className="px-3 py-1">
                            {user.role.toUpperCase()}
                        </Badge>
                    </div>

                    <div className="space-y-4">
                        {/* Username Element */}
                        <div className="space-y-2">
                            <Label htmlFor="username">Username</Label>
                            <div className="relative">
                                <User className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                <Input
                                    id="username"
                                    defaultValue={user.username}
                                    className="pl-10 focus-visible:ring-primary"
                                />
                            </div>
                        </div>

                        {/* Email Element (Read-only usually for security) */}
                        <div className="space-y-2 opacity-80">
                            <Label htmlFor="email">Email Address</Label>
                            <div className="relative">
                                <Mail className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                                <Input
                                    id="email"
                                    type="email"
                                    value={user.email}
                                    disabled
                                    className="pl-10 bg-slate-50 cursor-not-allowed"
                                />
                            </div>
                            <p className="text-[12px] text-slate-500 italic px-1">
                                Email is managed by your institution and cannot be changed.
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter className="flex flex-col gap-3">
                    {/* Save Changes Feat */}
                    <Button className="w-full font-semibold transition-all hover:scale-[1.01]">
                        Save Changes
                    </Button>
                    <Button variant="ghost" className="w-full text-slate-500">
                        <Link to='/home' >
                            Cancel
                        </Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    )
}//Components