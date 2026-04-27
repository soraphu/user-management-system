import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { type VariantProps } from "class-variance-authority";

type ButtonProps = React.ComponentPropsWithoutRef<typeof Button>;

interface NavigateButtonProps extends ButtonProps {
    to: string;
    label: string;
}

export function NavigateButton({ to, label, ...props }: NavigateButtonProps) {
    return (
        <Button asChild {...props}>
            <Link to={to}>{label}</Link>
        </Button>
    );
}