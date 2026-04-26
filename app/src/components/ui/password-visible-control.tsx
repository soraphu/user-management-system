import { useState, type JSX } from "react";
import { Eye, EyeClosed } from 'lucide-react';
import { Input } from "@/components/ui/input"

const InputPasswordWithVisibleControl = ({ id }: { id: string | undefined }) => {
    const [passwordVisible, setPasswordVisible] = useState(false);
    const passwordVisibleControlButton: JSX.Element = <button
        type="button"
        onClick={() => setPasswordVisible(!passwordVisible)}
        className="absolute right-3 top-1/4 size-fit"
    >
        {passwordVisible ? (
            <EyeClosed className="h-4 w-4" />
        ) : (
            <Eye className="h-4 w-4" />
        )}
    </button>;

    return (
        <div className="relative" >
            <Input id={id} type={passwordVisible ? "text" : "password"} required />
            {passwordVisibleControlButton}
        </div>
    )
}//InputPasswordWithVisibleControl component.

export default InputPasswordWithVisibleControl