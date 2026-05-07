import { FaGithub } from "react-icons/fa";
import LoginForm from "../components/login-form"
import { useEffect } from "react";
import { useUserAction } from "@/helper/useUserAction";
import Swal from "sweetalert2";
import { swalConfirmButtonColor } from "@/helper/config";

const LoginPage = () => {
    const { ensureLoggedIn } = useUserAction();

    useEffect(() => {
        ensureLoggedIn();

        Swal.fire({
            icon: "question",
            title: "NOTICE",
            text: "Welcome! Please note that this is a live demo running on a stateless server. To keep the demo fast and secure, all registered accounts are temporary and will be cleared when the server restarts. Feel free to explore the management features!",
            confirmButtonColor: swalConfirmButtonColor,
            confirmButtonText: "I Understand"
        });
    }, []);

    return (
        <div className="grid min-h-svh lg:grid-cols-2 md:grid-cols-1">
            <div className="flex flex-col gap-4 p-6 md:p-10">
                <div className="flex justify-center gap-2 md:justify-start">
                    <a href="https://github.com/soraphu" target="_blank" className="flex items-center gap-2 font-medium">
                        <div className="flex size-6 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <FaGithub size={18} className="hover:text-gray-400 transition-colors" />
                        </div>
                        Soraphu - Github
                    </a>
                </div>

                <div className="flex flex-1 items-center justify-center">
                    <div className="w-full max-w-xs">
                        <LoginForm />
                    </div>
                </div>

            </div>

            <div className="relative bg-gradient-to-br from-primary/10 to-primary/5 lg:flex flex-col items-center justify-center p-10">
                <div className="max-w-md space-y-6">
                    <div>
                        <h2 className="text-3xl font-bold mb-2">Test Accounts</h2>
                        <p className="text-muted-foreground">Use these credentials to explore the system</p>
                    </div>

                    <div className="space-y-4">
                        <div className="bg-card rounded-lg p-4 border border-border">
                            <h3 className="font-semibold text-lg mb-3 flex items-center gap-2">
                                <span className="inline-flex items-center justify-center size-6 rounded-full bg-primary text-primary-foreground text-sm font-bold">👑</span>
                                Admin Account
                            </h3>
                            <div className="space-y-2 text-sm">
                                <div>
                                    <p className="text-muted-foreground">Email</p>
                                    <p className="font-mono bg-muted p-2 rounded">admin@test.com</p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">Password</p>
                                    <p className="font-mono bg-muted p-2 rounded">Admin@project</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-card rounded-lg p-4 border border-border">
                            <h3 className="font-semibold text-lg mb-3 flex items-center gap-2">
                                <span className="inline-flex items-center justify-center size-6 rounded-full bg-secondary text-secondary-foreground text-sm font-bold">👤</span>
                                Sample User Account
                            </h3>
                            <div className="space-y-2 text-sm">
                                <div>
                                    <p className="font-mono bg-muted p-2 rounded">Can access to website by register and test system flow.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-blue-50 dark:bg-blue-950 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                        <p className="text-xs text-blue-900 dark:text-blue-100">
                            💡 <strong>Tip:</strong> All test accounts are pre-verified and ready to use. Feel free to explore the admin dashboard and user features.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    )//return HTML;
}//Entire login page.

export default LoginPage