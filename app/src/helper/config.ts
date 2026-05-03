import axios from 'axios';

export const swalConfirmButtonColor = "#3085d6";

export const isDevMode = true;

const apiBaseURL = import.meta.env.VITE_AUTH_API_BASE_URL;

export const API_AUTH = {
    Register: `${apiBaseURL}/register`,
    Login: `${apiBaseURL}/login`,
    GetInbox: `${apiBaseURL}/inbox`,
    VerifyEmailSent: `${apiBaseURL}/email/verify-request`,
    VerifiedEmail: `${apiBaseURL}/email/verified`,
    MarkMailAsRead: `${apiBaseURL}/inbox/mark-as-read`,
    ForgetPassword: `${apiBaseURL}/password/forget`,
    ResetPassword: `${apiBaseURL}/password/reset`,
};

export const API_ACTION = {
    FetchUser: `${apiBaseURL}/user/info`,
}

export const useCookie = axios.create({
    withCredentials: true, // Crucial for your HttpOnly cookies
    headers: {
        'Content-Type': 'application/json',
    },
});