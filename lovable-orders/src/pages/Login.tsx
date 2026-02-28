import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Eye, EyeOff, ArrowLeft } from "lucide-react";
import { Link, useNavigate } from "react-router-dom";
import { useToast } from "@/hooks/use-toast";
import { useAuth } from "@/contexts/AuthContext";
import { ApiError } from "@/types/api";

const Login = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const navigate = useNavigate();
  const { toast } = useToast();
  const { login, register } = useAuth();

  const [loginData, setLoginData] = useState({
    email: "",
    password: "",
  });

  const [signupData, setSignupData] = useState({
    name: "",
    surname: "",
    birth_date: "",
    email: "",
    password: "",
    confirmPassword: "",
  });

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    try {
      await login(loginData.email, loginData.password);
      toast({ title: "Login realizado!", description: "Bem-vindo de volta ao MarketPlace" });
      navigate("/");
    } catch (err) {
      const message =
        err instanceof ApiError && err.status === 401
          ? "E-mail ou senha inválidos"
          : "Erro ao entrar. Tente novamente.";
      toast({ title: "Erro", description: message, variant: "destructive" });
    } finally {
      setIsLoading(false);
    }
  };

  const handleSignup = async (e: React.FormEvent) => {
    e.preventDefault();

    if (signupData.password !== signupData.confirmPassword) {
      toast({ title: "Erro", description: "As senhas não coincidem", variant: "destructive" });
      return;
    }

    setIsLoading(true);
    try {
      await register({
        name: signupData.name,
        surname: signupData.surname,
        birth_date: signupData.birth_date,
        email: signupData.email,
        password: signupData.password,
        password_confirmation: signupData.confirmPassword,
      });
      toast({ title: "Conta criada!", description: "Seja bem-vindo ao MarketPlace" });
      navigate("/");
    } catch (err) {
      let description = "Erro ao criar conta. Tente novamente.";
      if (err instanceof ApiError) {
        if (err.body.details) {
          const firstField = Object.values(err.body.details)[0];
          description = firstField?.[0] ?? description;
        } else {
          description = err.body.message;
        }
      }
      toast({ title: "Erro", description, variant: "destructive" });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-background flex flex-col">
      {/* Header */}
      <div className="bg-brand py-4">
        <div className="container">
          <div className="flex items-center gap-4">
            <Link 
              to="/" 
              className="p-2 -ml-2 hover:bg-brand-hover rounded-lg transition-colors"
            >
              <ArrowLeft className="w-6 h-6 text-primary-foreground" />
            </Link>
            <h1 className="text-xl font-bold text-primary-foreground">MarketPlace</h1>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="flex-1 flex items-center justify-center p-4">
        <div className="w-full max-w-md animate-fade-in">
          <div className="bg-card rounded-xl shadow-card p-6 sm:p-8">
            <Tabs defaultValue="login" className="w-full">
              <TabsList className="grid w-full grid-cols-2 mb-6">
                <TabsTrigger value="login">Entrar</TabsTrigger>
                <TabsTrigger value="signup">Criar conta</TabsTrigger>
              </TabsList>

              <TabsContent value="login" className="space-y-4">
                <div className="text-center mb-6">
                  <h2 className="text-2xl font-bold text-foreground">Bem-vindo!</h2>
                  <p className="text-muted-foreground mt-1">
                    Entre para continuar comprando
                  </p>
                </div>

                <form onSubmit={handleLogin} className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="login-email">E-mail</Label>
                    <Input
                      id="login-email"
                      type="email"
                      placeholder="seu@email.com"
                      value={loginData.email}
                      onChange={(e) => setLoginData({ ...loginData, email: e.target.value })}
                      required
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="login-password">Senha</Label>
                    <div className="relative">
                      <Input
                        id="login-password"
                        type={showPassword ? "text" : "password"}
                        placeholder="••••••••"
                        value={loginData.password}
                        onChange={(e) => setLoginData({ ...loginData, password: e.target.value })}
                        required
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                      >
                        {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                      </button>
                    </div>
                  </div>

                  <button
                    type="button"
                    className="text-sm text-brand hover:underline"
                  >
                    Esqueceu sua senha?
                  </button>

                  <Button 
                    type="submit" 
                    className="w-full bg-brand hover:bg-brand-hover text-primary-foreground font-semibold shadow-button"
                    disabled={isLoading}
                  >
                    {isLoading ? "Entrando..." : "Entrar"}
                  </Button>
                </form>
              </TabsContent>

              <TabsContent value="signup" className="space-y-4">
                <div className="text-center mb-6">
                  <h2 className="text-2xl font-bold text-foreground">Criar conta</h2>
                  <p className="text-muted-foreground mt-1">
                    Cadastre-se para começar a comprar
                  </p>
                </div>

                <form onSubmit={handleSignup} className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="signup-name">Nome</Label>
                    <Input
                      id="signup-name"
                      type="text"
                      placeholder="Seu primeiro nome"
                      value={signupData.name}
                      onChange={(e) => setSignupData({ ...signupData, name: e.target.value })}
                      required
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="signup-surname">Sobrenome</Label>
                    <Input
                      id="signup-surname"
                      type="text"
                      placeholder="Sobrenome"
                      value={signupData.surname}
                      onChange={(e) => setSignupData({ ...signupData, surname: e.target.value })}
                      required
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="signup-birth">Data de nascimento</Label>
                    <Input
                      id="signup-birth"
                      type="date"
                      value={signupData.birth_date}
                      onChange={(e) => setSignupData({ ...signupData, birth_date: e.target.value })}
                      required
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="signup-email">E-mail</Label>
                    <Input
                      id="signup-email"
                      type="email"
                      placeholder="seu@email.com"
                      value={signupData.email}
                      onChange={(e) => setSignupData({ ...signupData, email: e.target.value })}
                      required
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="signup-password">Senha</Label>
                    <div className="relative">
                      <Input
                        id="signup-password"
                        type={showPassword ? "text" : "password"}
                        placeholder="••••••••"
                        value={signupData.password}
                        onChange={(e) => setSignupData({ ...signupData, password: e.target.value })}
                        required
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                      >
                        {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                      </button>
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="signup-confirm">Confirmar senha</Label>
                    <Input
                      id="signup-confirm"
                      type={showPassword ? "text" : "password"}
                      placeholder="••••••••"
                      value={signupData.confirmPassword}
                      onChange={(e) => setSignupData({ ...signupData, confirmPassword: e.target.value })}
                      required
                    />
                  </div>

                  <Button 
                    type="submit" 
                    className="w-full bg-brand hover:bg-brand-hover text-primary-foreground font-semibold shadow-button"
                    disabled={isLoading}
                  >
                    {isLoading ? "Criando conta..." : "Criar conta"}
                  </Button>
                </form>
              </TabsContent>
            </Tabs>
          </div>

          <p className="text-center text-sm text-muted-foreground mt-6">
            Ao continuar, você concorda com nossos{" "}
            <button className="text-brand hover:underline">Termos de Uso</button>
            {" "}e{" "}
            <button className="text-brand hover:underline">Política de Privacidade</button>
          </p>
        </div>
      </div>
    </div>
  );
};

export default Login;
