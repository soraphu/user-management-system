import { API_AUTH, reqWithCookie } from '@/helper/config';
import { getCatchMessage } from '@/helper/request_handler';
import { createContext, useState, type ReactNode, useContext } from 'react';

// Define the shape of the Context
interface AuthContextType {
    accessToken: string | null;
    setAccessToken: (token: string | null) => void;
    user: UserType | null;
    setUser: (user: UserType | null) => void;
    handleLogout: () => void;
}

interface UserType {
    username: string;
    email: string;
    role: string;
}

// Create the context with a default value of undefined
const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [accessToken, setAccessToken] = useState<string | null>(null);
    const [user, setUser] = useState<UserType | null>(null);

    const handleLogout = async () => {
        try {
            // Call server to clear the HttpOnly Refresh Token cookie
            await reqWithCookie.post(API_AUTH.Logout);
        } catch (error) {
            getCatchMessage(error);
        } finally {
            // This ensures the UI updates immediately.
            setAccessToken(null);
            setUser(null);

            window.location.href = '/';
        }
    };

    return (
        <AuthContext.Provider value={{ accessToken, setAccessToken, user, setUser, handleLogout }}>
            {children}
        </AuthContext.Provider>
    );
};

// Custom hook to make using the context easier and safer
export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};