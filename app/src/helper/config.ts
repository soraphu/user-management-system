export const swalConfirmButtonColor = "#3085d6";

const apiBaseURL = import.meta.env.VITE_AUTH_API_BASE_URL;

export const API_ENDPOINTS = {
    Register: `${apiBaseURL}/register`,
    Login: `${apiBaseURL}/login`,
    GetInbox: `${apiBaseURL}/inbox`,
    VerifyEmailSent: `${apiBaseURL}/email/verify-request`,
    VerifiedEmail: `${apiBaseURL}/email/verified`,
    MarkMailAsRead: `${apiBaseURL}/inbox/mark-as-read`,
    ForgetPassword: `${apiBaseURL}/password/forget`,
    ResetPassword: `${apiBaseURL}/password/reset`
};