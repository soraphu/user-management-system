const isDevMode: boolean = import.meta.env.VITE_DEV_MODE === "true";

export function consoleLogOnDev(message: any) {
    if (isDevMode) {
        console.log(message);
    }
} //Log on dev mode only.

export function consoleErrorOnDev(message: any) {
    if (isDevMode) {
        console.error(message);
    }
} //Log on dev mode only.