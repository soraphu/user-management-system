export const swalConfirmButtonColor = "#3085d6";

export const devMode = import.meta.env.VITE_DEV_MODE === "true";

const apiBaseURL = import.meta.env.VITE_AUTH_API_BASE_URL;
export const REGISTER_API = apiBaseURL + "/register";
export const LOGIN_API = apiBaseURL + "/login";
export const GET_INBOX_API = apiBaseURL + "/inbox";
export const VERIFY_EMAIL_REQUEST_API = apiBaseURL + "/email/verify-request";
export const VERIFIED_EMAIL_API = apiBaseURL + "/email/verified";
export const UPDATE_MAIL_READ_API = apiBaseURL + "/inbox/mark-as-read";
export const FORGET_PASSWORD_API = apiBaseURL + "/password/forget";
export const RESET_PASSWORD_API = apiBaseURL + "/password/reset";