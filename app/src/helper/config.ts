import axios from 'axios';

export const swalConfirmButtonColor = "#3085d6";

export const isDevMode = import.meta.env.VITE_DEV_MODE === 'true';

const apiBaseURL = import.meta.env.VITE_AUTH_API_BASE_URL;

export const API_CHECK_SERVER_RESPONSE = apiBaseURL;

export const API_AUTH = {
    Register: `${apiBaseURL}/register`,
    Login: `${apiBaseURL}/login`,
    GetInbox: `${apiBaseURL}/inbox`,
    VerifyEmailSent: `${apiBaseURL}/email/verify-request`,
    VerifiedEmail: `${apiBaseURL}/email/verified`,
    MarkMailAsRead: `${apiBaseURL}/inbox/mark-as-read`,
    ForgetPassword: `${apiBaseURL}/password/forget`,
    ResetPassword: `${apiBaseURL}/password/reset`,
    RefreshToken: `${apiBaseURL}/login/refresh-token`,
    Logout: `${apiBaseURL}/logout`,
};

export const API_ACTION = {
    FetchUser: `${apiBaseURL}/user/fetch-user`,
    ChangeUsername: `${apiBaseURL}/user/change-username`,
}

export const API_ADMIN = {
    FetchAllUsers: `${apiBaseURL}/admin/fetch-all-users`,
    EditUserInfo: `${apiBaseURL}/admin/edit-user-info`
}

export const reqWithCookie = axios.create({
    withCredentials: true, // Crucial for your HttpOnly cookies
    headers: {
        'Content-Type': 'application/json',
    },
});