import { MdContentCopy } from "react-icons/md";
import { toast } from "sonner";

const CopyToClipboradIcon = ({ copyContent, className }: { copyContent: string; className?: string }) => {
    const handleCopy = async () => {
        try {
            if (!navigator.clipboard) {
                throw new Error("Clipboard API not supported");
            }

            await navigator.clipboard.writeText(copyContent);
            toast.success("Copied to clipboard.");
        } catch (error) {
            toast.error("Failed to copy. Please try again.");
        }
    };

    return (
        <button
            type="button"
            onClick={handleCopy}
            className={`${className} active:translate-y-px`}
            aria-label="Copy to clipboard"
        >
            <MdContentCopy />
        </button>
    );
};

export default CopyToClipboradIcon