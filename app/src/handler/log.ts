import Swal from "sweetalert2";

export function consoleLogOnDev(message: any) {
    const isDevMode: boolean = import.meta.env.VITE_DEV_MODE === "true";
    if (isDevMode) {
        console.log(message);
    }
} //Log on dev mode only.

export function consoleErrorOnDev(message: any) {
    const isDevMode: boolean = import.meta.env.VITE_DEV_MODE === "true";
    if (isDevMode) {
        console.error(message);
    }
} //Log on dev mode only.

export function swalAlertNavigate(
    { icon, title, text, confirmButtonText }: { icon: "success" | "error" | "warning" | "info" | "question", title: string, text: string, confirmButtonText?: string }
) {
    Swal.fire({
        icon: icon,
        title: title,
        confirmButtonColor: "#4169E1",
        confirmButtonText: confirmButtonText || "I Understand",
        text: text
    });
}