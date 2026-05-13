import { useEffect, useRef, useState } from 'react';

import { Link } from 'react-router-dom';
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
    DialogDescription,
    DialogClose
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { useUserAction } from '@/helper/useUserAction';
import { API_ADMIN } from '@/helper/config';
import { toast } from 'sonner';
import { useAuth } from '@/auth/AuthContext';
import { getCatchMessage } from '@/helper/request_handler';
import { consoleLogDevMode } from '@/helper/log';
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from "@/components/ui/hover-card"
import { HashLoader } from 'react-spinners';

// --- Types & Mock Data ---
interface UsersType {
    id: number;
    username: string;
    email: string;
    role: string;
    verified: boolean;
}

const AdminDashboardPage = () => {
    const [searchTerm, setSearchTerm] = useState("");
    const [roleFilter, setRoleFilter] = useState("all");
    const [currentPage, setCurrentPage] = useState(1);
    const [users, setUsers] = useState<UsersType[] | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const usersPerPage = 10;

    const { user: admin } = useAuth();
    const { fetchUser, handleReqAccessAction } = useUserAction();

    useEffect(() => {
        handleFetchAllUsers();
    }, []);

    const handleFetchAllUsers = async () => {
        try {
            const res = await handleReqAccessAction({
                method: 'GET',
                url: API_ADMIN.FetchAllUsers
            });

            //Request sucessfully.
            const getUsers: UsersType[] = res?.data.users;
            const resMessage = res?.data.message;

            consoleLogDevMode(resMessage);

            setUsers(getUsers);
        } catch (error) {
            const errMessage = getCatchMessage(error);
            toast.error(errMessage);
        } finally {
            setLoading(false);
        }
    }; //handleFetchAllUsers().

    if (!admin) fetchUser();

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

    const HeaderSection = ({ admin }: { admin: any }) => {
        return (
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-yellow-500/20 pb-6">
                <Link to='/home'>
                    <div >
                        <h1 className="text-3xl font-black tracking-tighter text-yellow-500 uppercase">
                            User Management System
                        </h1>
                        <p className="text-slate-500 text-sm font-mono">{"admin: "} {admin?.email}</p>
                    </div>
                </Link>

                <div className="flex items-center gap-2">
                    <HoverCard>
                        <HoverCardTrigger>
                            <Badge variant="outline" className="border-yellow-500/50 text-yellow-500 bg-yellow-500/5 cursor-zoom-in">
                                ADMINISTRATIVE GURAD
                            </Badge>
                        </HoverCardTrigger>
                        <HoverCardContent className="w-80">
                            <div className="flex justify-between space-x-4">
                                <div className="space-y-1">
                                    <p className="text-sm text-muted-foreground">
                                        All system-impact actions are protected by real-time role verification. Every request triggers a server-side check that cross-references your session token against the live database to ensure active administrative privileges before processing.
                                    </p>
                                </div>
                            </div>
                        </HoverCardContent>
                    </HoverCard>

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
        const [isChanging, setIsChanging] = useState<boolean>(false);
        const initUsername = user?.username;
        const initRole = user?.role;
        const currUsername = useRef<string>(initUsername);
        const currRole = useRef<string>(initRole);

        const handleIsChangeCondition = () => {
            const newUsername = currUsername.current;
            const newRole = currRole.current;

            if (newUsername !== initUsername || newRole !== initRole) {
                setIsChanging(true);
            }
            if (newUsername === initUsername && newRole === initRole) {
                setIsChanging(false);
            }
        }//handleOnChangeInfo

        const handleExecuteTheChange = async () => {

            const executeData = {
                uid: user?.id,
                username: currUsername.current,
                role: currRole.current
            };

            consoleLogDevMode(executeData);

            try {
                const res = await handleReqAccessAction({
                    method: 'PATCH',
                    url: API_ADMIN.EditUserInfo,
                    body: executeData
                });

                const resMessage = res?.data.message;
                toast.success(resMessage);

                handleFetchAllUsers();
            } catch (error) {
                const errMessage = getCatchMessage(error);
                toast.error(errMessage);
            }
        } //onExecuteChange

        return (
            <Dialog>
                <DialogTrigger asChild>
                    <Button variant="ghost" size="icon" className="hover:bg-yellow-500/10 hover:text-yellow-500 cursor-pointer">
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
                            <Input id="username" defaultValue={user?.username} onChange={(e) => {
                                currUsername.current = e.target.value;
                                handleIsChangeCondition();
                            }} className="col-span-3 bg-black border-slate-700" />
                        </div>
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label className="text-right">Access Role</Label>
                            <Select defaultValue={user?.role} onValueChange={(selectValue) => {
                                currRole.current = selectValue;
                                handleIsChangeCondition();
                            }} >
                                <SelectTrigger id='role' className="col-span-3 bg-black border-slate-700 cursor-pointer">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent className="bg-slate-900 border-slate-800 text-slate-100 " >
                                    <SelectItem value="user" className='cursor-pointer'>User (Standard)</SelectItem>
                                    <SelectItem value="admin" className='cursor-pointer' >Administrator (Elevated)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <DialogFooter className='grid grid-cols-1' >
                        <DialogClose className='relative grid grid-cols-2 gap-4' >
                            <Button className="bg-white text-black hover:bg-gray-300 font-bold cursor-pointer">
                                Cancel
                            </Button>
                            <Button
                                onClick={handleExecuteTheChange}
                                disabled={!isChanging}
                                className="bg-yellow-500 text-black hover:bg-orange-500 hover:text-black font-bold cursor-pointer"
                            >
                                Execute
                            </Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog >
        )
    }//EditUserDialogSection

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
                    <Select onValueChange={
                        (value) => {
                            setRoleFilter(value);
                            setCurrentPage(1);
                        }
                    } defaultValue="all">
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
                        Showing {filteredUsers?.length || '0'} Results
                    </div>
                </div>

                {/* User List */}
                {loading ? <HashLoader className='relative justify-self-center mt-20 mb-20' color='#FFFFFF' size={60} />
                    :
                    <UserListContainer displayUsers={displayUsers} />}

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