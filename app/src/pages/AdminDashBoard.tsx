import { useEffect, useState } from 'react';
import {
    Search,
    Filter,
    Edit3,
    ChevronLeft,
    ChevronRight,
    ShieldCheck,
    User as UserIcon,
    Mail,
    CheckCircle2,
    XCircle,
} from 'lucide-react';
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogTrigger,
    DialogDescription
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { useUserAction } from '@/helper/useUserAction';
import { API_ACTION } from '@/helper/config';
import { toast } from 'sonner';
import { useAuth } from '@/auth/AuthContext';
import { getCatchMessage } from '@/helper/request_handler';
import { consoleLogDevMode } from '@/helper/log';

// --- Types & Mock Data ---
type UserRole = 'user' | 'admin';

interface UsersType {
    id: number;
    username: string;
    email: string;
    role: UserRole;
    verified: boolean;
}

// Generate 30 test users for your UMS testing
// const users: UsersType[] = Array.from({ length: 30 }, (_, i) => ({
//     id: i + 1,
//     username: `user_tester_${i + 1}`,
//     email: `tester${i + 1}@live.rmutl.ac.th`,
//     role: i % 5 === 0 ? 'admin' : 'user',
//     verified: i % 3 !== 0,
// }));

const AdminDashboardPage = () => {
    const [searchTerm, setSearchTerm] = useState("");
    const [roleFilter, setRoleFilter] = useState("all");
    const [currentPage, setCurrentPage] = useState(1);
    const [users, setUsers] = useState<UsersType[] | null>(null);
    const usersPerPage = 10;

    const { user: admin } = useAuth();
    const { fetchUser, handleReqAccessAction } = useUserAction();

    useEffect(() => {

        const handleFetchAllUsers = async () => {
            try {
                const res = await handleReqAccessAction({
                    method: 'GET',
                    url: API_ACTION.ADMIN_FetchAllUsers
                });

                //Request sucessfully.
                const getUsers: UsersType[] = res?.data.users;
                const resMessage = res?.data.message;

                consoleLogDevMode(resMessage);

                setUsers(getUsers);
            } catch (error) {
                const errMessage = getCatchMessage(error);
                toast.error(errMessage);
            }//trycatch.
        }; //handleFetchAllUsers().

        if (!admin) fetchUser();

        handleFetchAllUsers();
    }, []);

    // Filter Logic
    const filteredUsers = users?.filter((user) => {
        const matchesSearch =
            user?.username?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            user?.email?.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesRole = roleFilter === "all" || user?.role === roleFilter;
        return matchesSearch && matchesRole;
    });

    // Pagination Logic
    const indexOfLastUser = currentPage * usersPerPage;
    const indexOfFirstUser = indexOfLastUser - usersPerPage;
    const displayUsers = filteredUsers?.slice(indexOfFirstUser, indexOfLastUser);
    const totalPages = Math.ceil((filteredUsers?.length || 0) / usersPerPage);

    return (
        <div className="min-h-screen bg-[#0a0a0a] text-slate-200 p-6 font-sans">
            <div className="max-w-6xl mx-auto space-y-6">

                {/* Header Section */}
                <HeaderSection admin={admin} />

                {/* Search & Filter Bar */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 bg-[#111] p-4 rounded-xl border border-white/5 shadow-2xl">
                    <div className="md:col-span-2 relative">
                        <Search className="absolute left-3 top-2 h-4 w-4 text-slate-500" />
                        <Input
                            placeholder="Search by username email"
                            className="pl-10 bg-black border-slate-800 focus:border-yellow-500/50 transition-all"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <Select onValueChange={setRoleFilter} defaultValue="all">
                        <SelectTrigger className="bg-black border-slate-800">
                            <div className="flex items-center gap-2">
                                <Filter className="h-4 w-4 text-yellow-500" />
                                <SelectValue placeholder="Filter Role" />
                            </div>
                        </SelectTrigger>
                        <SelectContent className="bg-slate-900 border-slate-800 text-slate-200">
                            <SelectItem value="all">All Roles</SelectItem>
                            <SelectItem value="admin">Admins Only</SelectItem>
                            <SelectItem value="user">Users Only</SelectItem>
                        </SelectContent>
                    </Select>
                    <div className="text-xs text-white flex items-center justify-center border border-dashed border-yellow-200 rounded-md">
                        Showing {filteredUsers?.length} Results
                    </div>
                </div>

                {/* User List */}
                <UserListContainer displayUsers={displayUsers} />

                {/* Pagination Controls */}
                <div className="flex items-center justify-between pt-4 border-t border-white/5 text-slate-500">
                    <p className="text-xs font-mono">Page {currentPage} of {totalPages}</p>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={currentPage === 1}
                            onClick={() => setCurrentPage(prev => prev - 1)}
                            className="bg-black border-slate-800"
                        >
                            <ChevronLeft size={16} /> Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={currentPage === totalPages}
                            onClick={() => setCurrentPage(prev => prev + 1)}
                            className="bg-black border-slate-800"
                        >
                            Next <ChevronRight size={16} />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdminDashboardPage;

const HeaderSection = ({ admin }: { admin: any }) => {
    return (
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-yellow-500/20 pb-6">
            <div>
                <h1 className="text-3xl font-black tracking-tighter text-yellow-500 uppercase">
                    Admin Dashboard
                </h1>
                <p className="text-slate-500 text-sm font-mono">{"admin: "} {admin?.email}</p>
            </div>

            <div className="flex items-center gap-2">
                <Badge variant="outline" className="border-yellow-500/50 text-yellow-500 bg-yellow-500/5">
                    SECURE SESSION
                </Badge>
            </div>
        </div>
    )
}

const UserListContainer = ({ displayUsers }: { displayUsers: UsersType[] | undefined | null }) => {
    return (
        <div className="space-y-3">
            {displayUsers?.map((user) => (
                <div
                    key={user?.id}
                    className="flex items-center justify-between p-4 bg-[#161616] border border-white/5 rounded-lg hover:border-yellow-500/30 transition-all group"
                >
                    <div className="flex items-center gap-4">
                        <div className="p-3 bg-black rounded-full border border-slate-800">
                            {user?.role === 'admin' ?
                                <ShieldCheck className="h-5 w-5 text-yellow-500" /> :
                                <UserIcon className="h-5 w-5 text-slate-400" />
                            }
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="font-bold text-slate-100">{user?.username}</span>
                                <Badge className={user?.role === 'admin' ? "bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500/10 border-none text-[10px]" : "bg-slate-800 text-slate-400 border-none text-[10px]"}>
                                    {user?.role?.toUpperCase()}
                                </Badge>
                            </div>
                            <div className="flex flex-col md:flex-row items-center gap-3 text-xs text-slate-500 mt-1 font-mono">
                                <span className="flex items-center gap-1"><Mail size={12} />{user?.email}</span>
                                <span className="flex items-center gap-1">
                                    {user?.verified ?
                                        <CheckCircle2 size={12} className="text-green-500" /> :
                                        <XCircle size={12} className="text-red-500" />
                                    }
                                    {user?.verified ? 'Verified' : 'Unverified'}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Edit User - Dialog */}
                    <EditUserDialogSection user={user} />
                </div>
            ))}
        </div>
    )
}//UserListContainer

const EditUserDialogSection = ({ user }: { user: UsersType }) => {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="ghost" size="icon" className="hover:bg-yellow-500/10 hover:text-yellow-500">
                    <Edit3 className="h-5 w-5" />
                </Button>
            </DialogTrigger>
            <DialogContent className="bg-slate-900 border-slate-800 text-slate-100">
                <DialogHeader>
                    <DialogTitle className="text-yellow-500">Edit User Authority</DialogTitle>
                    <DialogDescription className="text-slate-400">
                        Modifying record for UID: {user?.id}
                    </DialogDescription>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="username" className="text-right">Username</Label>
                        <Input id="username" defaultValue={user?.username} className="col-span-3 bg-black border-slate-700" />
                    </div>
                    <div className="grid grid-cols-4 items-center gap-4">
                        <Label className="text-right">Access Role</Label>
                        <Select defaultValue={user?.role}>
                            <SelectTrigger className="col-span-3 bg-black border-slate-700">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent className="bg-slate-900 border-slate-800 text-slate-100">
                                <SelectItem value="user">User (Standard)</SelectItem>
                                <SelectItem value="admin">Administrator (Elevated)</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <DialogFooter>
                    <Button className="bg-yellow-500 text-black hover:bg-yellow-600 font-bold">
                        Commit Changes
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}//EditUserDialogSection