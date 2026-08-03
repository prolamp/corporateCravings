import { Moon, Sun } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useAppearance } from '@/hooks/use-appearance';

export function AppearanceToggle({ className }: { className?: string }) {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const next = resolvedAppearance === 'dark' ? 'light' : 'dark';

    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className={className}
                    onClick={() => updateAppearance(next)}
                    aria-label={`Switch to ${next} mode`}
                >
                    {resolvedAppearance === 'dark' ? (
                        <Sun className="size-4" />
                    ) : (
                        <Moon className="size-4" />
                    )}
                </Button>
            </TooltipTrigger>
            <TooltipContent>
                {next === 'dark' ? 'Dark mode' : 'Light mode'}
            </TooltipContent>
        </Tooltip>
    );
}
