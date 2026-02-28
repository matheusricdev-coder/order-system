import { Check, LogIn } from "lucide-react";
import { useNavigate } from "react-router-dom";

const weekDays = ["D", "S", "T", "Q", "Q", "S", "S"];

interface DailyLoginBarProps {
  loginDays?: boolean[];
  currentDay?: number;
  totalCoins?: number;
  isAuthenticated?: boolean;
}

const DailyLoginBar = ({
  loginDays = Array(7).fill(false),
  currentDay = new Date().getDay(),
  totalCoins = 0,
  isAuthenticated = false,
}: DailyLoginBarProps) => {
  const navigate = useNavigate();
  const completedDays = loginDays.filter(Boolean).length;

  if (!isAuthenticated) {
    return (
      <div className="bg-gradient-to-r from-brand-light to-card border-b border-border">
        <div className="container py-4">
          <div className="flex items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <span className="text-2xl">🪙</span>
              <div>
                <h3 className="text-sm font-bold text-foreground">Login Diário</h3>
                <p className="text-xs text-muted-foreground">
                  Entre com sua conta para ter acesso a promoções e ganhar moedas diariamente!
                </p>
              </div>
            </div>
            <button
              onClick={() => navigate("/login")}
              className="flex-shrink-0 flex items-center gap-1.5 bg-brand text-primary-foreground text-xs font-semibold px-4 py-2 rounded-full hover:bg-brand-hover transition-colors"
            >
              <LogIn className="w-3.5 h-3.5" />
              Entrar
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-gradient-to-r from-brand-light to-card border-b border-border">
      <div className="container py-4">
        <div className="flex flex-col gap-3">
          {/* Header */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <span className="text-xl">🔥</span>
              <div>
                <h3 className="text-sm font-bold text-foreground">Login Diário</h3>
                <p className="text-xs text-muted-foreground">
                  Logue todos os dias para ganhar moedas e trocar por frete grátis ou produtos!
                </p>
              </div>
            </div>
            <div className="flex items-center gap-1.5 bg-brand px-3 py-1.5 rounded-full shadow-button">
              <span className="text-sm">🪙</span>
              <span className="text-xs font-bold text-primary-foreground">{totalCoins} moedas</span>
            </div>
          </div>

          {/* Days row */}
          <div className="flex items-center gap-2">
            <div className="flex items-center gap-1.5 flex-1">
              {weekDays.map((day, index) => {
                const isCompleted = loginDays[index];
                const isCurrent = index === currentDay;

                return (
                  <div
                    key={index}
                    className={`
                      relative flex-1 max-w-10 aspect-square rounded-full flex items-center justify-center text-xs font-bold transition-all
                      ${isCompleted
                        ? "bg-success text-success-foreground"
                        : isCurrent
                          ? "bg-brand text-primary-foreground ring-2 ring-brand ring-offset-1 ring-offset-background"
                          : "bg-secondary text-muted-foreground"
                      }
                    `}
                  >
                    {isCompleted ? <Check className="w-3.5 h-3.5" /> : day}
                  </div>
                );
              })}
            </div>
            <span className="text-xs text-muted-foreground font-medium whitespace-nowrap">
              {completedDays}/7 dias
            </span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DailyLoginBar;
