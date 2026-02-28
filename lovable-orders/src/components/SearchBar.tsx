import { Search } from "lucide-react";

interface SearchBarProps {
  value: string;
  onChange: (value: string) => void;
  onSearch?: () => void;
}

const SearchBar = ({ value, onChange, onSearch }: SearchBarProps) => {
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSearch?.();
  };

  return (
    <form onSubmit={handleSubmit} className="relative w-full">
      <input
        type="text"
        placeholder="Buscar produtos, marcas e muito mais..."
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="search-input pr-12 shadow-card"
      />
      <button
        type="submit"
        className="absolute right-1 top-1/2 -translate-y-1/2 p-2 bg-brand hover:bg-brand-hover rounded-md transition-colors"
      >
        <Search className="w-5 h-5 text-primary-foreground" />
      </button>
    </form>
  );
};

export default SearchBar;
