import { CheckCircle2, LogIn } from 'lucide-react';
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { useNavigate } from 'react-router-dom';

const VerifiedEmail = () => {
    const navigate = useNavigate();



    return (
        <div className="min-h-screen flex items-center justify-center bg-slate-50 p-4">
            <Card className="w-full max-w-md shadow-xl border-t-4 border-t-green-500">
                <CardHeader className="space-y-1 flex flex-col items-center">
                    <div className="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4 animate-in zoom-in duration-500">
                        <CheckCircle2 className="h-10 w-10 text-green-600" />
                    </div>
                    <CardTitle className="text-2xl font-bold text-slate-900">Email Verified!</CardTitle>
                    <CardDescription className="text-center text-base">
                        Your account has been successfully verified.
                        You now have full access to all features.
                    </CardDescription>
                </CardHeader>

                <CardContent className="pt-4">
                    <div className="bg-slate-100 rounded-lg p-4 mb-2">
                        <p className="text-sm text-slate-600 text-center">
                            Welcome to the <strong>User Management System</strong>.
                            You can now access to the website with this account.
                        </p>
                    </div>
                </CardContent>

                <CardFooter className="flex flex-col gap-3">
                    <Button
                        className="w-full bg-green-600 hover:bg-green-700"
                        onClick={() => navigate('/')}
                    >
                        Go to Login
                        <LogIn className="ml-2 h-4 w-4" />
                    </Button>

                </CardFooter>
            </Card>
        </div>
    );
};

export default VerifiedEmail;