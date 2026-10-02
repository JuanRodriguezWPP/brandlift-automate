import * as React from "react";

// ── Types ───────────────────────────────────────────────────────────────────

export type WizardStepState = "active" | "completed" | "pending";

export interface WizardStepData {
  id: string | number;
  title: string;
  description?: string;
}

export interface WizardContextValue {
  currentStep: number;
  setCurrentStep: (step: number) => void;
  totalSteps: number;
  isLoading: boolean;
}

// ── Context ─────────────────────────────────────────────────────────────────

const WizardContext = React.createContext<WizardContextValue | undefined>(undefined);

export function useWizard() {
  const context = React.useContext(WizardContext);
  if (!context) {
    throw new Error("useWizard must be used within a WizardStepper");
  }
  return context;
}

// ── Check Icon ──────────────────────────────────────────────────────────────

function CheckIcon({ className = "size-3.5" }: { className?: string }) {
  return (
    <svg
      className={className}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <polyline points="20 6 9 17 4 12" />
    </svg>
  );
}

// ── WizardCircle ────────────────────────────────────────────────────────────

interface WizardCircleProps {
  step: number;
  state: WizardStepState;
  isLoading: boolean;
}

function WizardCircle({ step, state, isLoading }: WizardCircleProps) {
  return (
    <div className="relative flex items-center justify-center">
      {state === "active" && (
        <span className="absolute -inset-1 rounded-full border-2 border-primary/20 animate-pulse" />
      )}
      <span
        className={`relative flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold transition-all duration-300 ${
          state === "completed"
            ? "bg-primary text-primary-foreground shadow-sm"
            : state === "active"
            ? "bg-primary text-primary-foreground ring-4 ring-primary/20 shadow-md"
            : "bg-muted/50 text-muted-foreground border border-border"
        }`}
      >
        {isLoading ? (
          <span className="h-3.5 w-3.5 rounded-full border-2 border-primary-foreground/30 border-t-primary-foreground animate-spin" />
        ) : state === "completed" ? (
          <CheckIcon className="h-4 w-4" />
        ) : (
          step
        )}
      </span>
    </div>
  );
}

// ── WizardConnector ─────────────────────────────────────────────────────────

interface WizardConnectorProps {
  state: WizardStepState;
}

function WizardConnector({ state }: WizardConnectorProps) {
  return (
    <div className="relative mx-3 mt-4 h-0.5 flex-1 min-w-[3rem] max-w-[6rem] overflow-hidden rounded-full bg-border">
      <div
        className={`absolute inset-y-0 left-0 bg-primary transition-all duration-500 ease-out ${
          state === "completed" ? "w-full" : "w-0"
        }`}
      />
    </div>
  );
}

// ── WizardStepItem ──────────────────────────────────────────────────────────

interface WizardStepItemProps {
  step: number;
  state: WizardStepState;
  title: string;
  description?: string;
  isLast: boolean;
  orientation: "horizontal" | "vertical";
  isLoading: boolean;
  onClick: () => void;
}

function WizardStepItem({
  step,
  state,
  title,
  description,
  isLast,
  orientation,
  isLoading,
  onClick,
}: WizardStepItemProps) {
  return (
    <div
      className={`group flex cursor-pointer transition-opacity ${
        orientation === "horizontal"
          ? "flex-col items-center text-center sm:flex-row sm:items-center sm:text-left gap-2.5"
          : "flex-row items-start gap-3"
      }`}
      onClick={onClick}
    >
      <div className="flex items-center">
        {orientation === "vertical" && (
          <div className="flex flex-col items-center">
            <WizardCircle step={step + 1} state={state} isLoading={isLoading} />
            {!isLast && (
              <div
                className={`mt-2 w-0.5 h-9 transition-colors duration-300 ${
                  state === "completed" ? "bg-primary" : "bg-border"
                }`}
              />
            )}
          </div>
        )}
        {orientation === "horizontal" && (
          <WizardCircle step={step + 1} state={state} isLoading={isLoading} />
        )}
      </div>

      <div className={orientation === "horizontal" ? "flex flex-col" : "pt-0.5 pb-6"}>
        <p
          className={`text-[13px] font-semibold tracking-tight transition-colors duration-200 ${
            state === "active"
              ? "text-foreground font-bold"
              : state === "completed"
              ? "text-foreground/80"
              : "text-muted-foreground/60"
          }`}
        >
          {title}
        </p>
        {description && (
          <p
            className={`text-[11px] leading-tight transition-colors duration-200 ${
              state === "pending" ? "text-muted-foreground/40" : "text-muted-foreground"
            }`}
          >
            {description}
          </p>
        )}
      </div>
    </div>
  );
}

// ── WizardStepper ───────────────────────────────────────────────────────────

export interface WizardStepperProps extends React.HTMLAttributes<HTMLDivElement> {
  steps: WizardStepData[];
  defaultStep?: number;
  currentStep?: number;
  onStepChange?: (step: number) => void;
  loading?: boolean;
  orientation?: "horizontal" | "vertical";
}

export function WizardStepper({
  steps,
  defaultStep = 0,
  currentStep: controlledStep,
  onStepChange,
  loading = false,
  orientation = "horizontal",
  className = "",
  ...props
}: WizardStepperProps) {
  const [internalStep, setInternalStep] = React.useState(defaultStep);
  const currentStep = controlledStep ?? internalStep;

  const setCurrentStep = React.useCallback(
    (step: number) => {
      if (controlledStep === undefined) setInternalStep(step);
      onStepChange?.(step);
    },
    [controlledStep, onStepChange]
  );

  return (
    <WizardContext.Provider
      value={{
        currentStep,
        setCurrentStep,
        totalSteps: steps.length,
        isLoading: loading,
      }}
    >
      <div className={`w-full ${className}`} {...props}>
        <div
          className={
            orientation === "horizontal"
              ? "flex items-center justify-center flex-wrap sm:flex-nowrap gap-2 sm:gap-0"
              : "flex flex-col gap-0"
          }
        >
          {steps.map((step, index) => {
            const state: WizardStepState =
              index < currentStep
                ? "completed"
                : index === currentStep
                ? "active"
                : "pending";

            return (
              <React.Fragment key={step.id}>
                <WizardStepItem
                  step={index}
                  state={state}
                  title={step.title}
                  description={step.description}
                  isLast={index === steps.length - 1}
                  orientation={orientation}
                  isLoading={loading && index === currentStep}
                  onClick={() => setCurrentStep(index)}
                />
                {index < steps.length - 1 && orientation === "horizontal" && (
                  <WizardConnector state={state} />
                )}
              </React.Fragment>
            );
          })}
        </div>
      </div>
    </WizardContext.Provider>
  );
}

export default WizardStepper;
