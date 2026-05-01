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