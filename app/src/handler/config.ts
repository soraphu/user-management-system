export const swalConfirmButtonColor = "#3085d6";

export const devMode = import.meta.env.VITE_DEV_MODE === "true";

const apiBaseURL = import.meta.env.VITE_AUTH_API_BASE_URL;

export const API_REGISTER = apiBaseURL + "/register";
export const API_LOGIN = apiBaseURL + "/login";
export const API_GET_INBOX = apiBaseURL + "/inbox";
export const API_VERIFY_EMAIL_SENT = apiBaseURL + "/email/verify-request";
export const API_VERIFIED_EMAIL = apiBaseURL + "/email/verified";
export const API_MARK_MAIL_AS_READ = apiBaseURL + "/inbox/mark-as-read";
export const API_FORGET_PASSWORD = apiBaseURL + "/password/forget";
export const API_RESET_PASSWORD = apiBaseURL + "/password/reset";