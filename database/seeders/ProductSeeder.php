<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ProductSeeder extends Seeder
{
    // Unsplash photo IDs mapped per category
    private const PRODUCTS = [
        // ── Eletrônicos ───────────────────────────────────────────────────────
        ['id' => 'aa000001-0000-0000-0000-000000000001', 'name' => 'Smartphone Samsung Galaxy S24', 'price' => 499900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 45,
         'images' => ['https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000001-0000-0000-0000-000000000002', 'name' => 'iPhone 15 Pro 256GB', 'price' => 899900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 20,
         'images' => ['https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000001-0000-0000-0000-000000000003', 'name' => 'Tablet Samsung Galaxy Tab A9', 'price' => 189900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 30,
         'images' => ['https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000001-0000-0000-0000-000000000004', 'name' => 'Smart TV 55" 4K LG OLED', 'price' => 399900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 15,
         'images' => ['https://images.unsplash.com/photo-1593359677879-a4bb92f4834a?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000001-0000-0000-0000-000000000005', 'name' => 'Fone de Ouvido Sony WH-1000XM5', 'price' => 179900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 60,
         'images' => ['https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000001-0000-0000-0000-000000000006', 'name' => 'Caixa de Som JBL Charge 5', 'price' => 119900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000001-0000-0000-0000-000000000007', 'name' => 'Smartwatch Apple Watch Series 9', 'price' => 349900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 25,
         'images' => ['https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000001-0000-0000-0000-000000000008', 'name' => 'Câmera Sony Alpha A7 IV', 'price' => 1499900, 'category' => '11111111-0000-0000-0000-000000000001', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 10,
         'images' => ['https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80']],

        // ── Informática ───────────────────────────────────────────────────────
        ['id' => 'aa000002-0000-0000-0000-000000000001', 'name' => 'Notebook Dell Inspiron 15 i7', 'price' => 379900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 20,
         'images' => ['https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000002-0000-0000-0000-000000000002', 'name' => 'MacBook Air M2 13"', 'price' => 799900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 12,
         'images' => ['https://images.unsplash.com/photo-1611186871525-97ec1f3db5a6?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000002-0000-0000-0000-000000000003', 'name' => 'Teclado Mecânico Keychron K2', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 50,
         'images' => ['https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000002-0000-0000-0000-000000000004', 'name' => 'Monitor LG UltraWide 34"', 'price' => 249900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 18,
         'images' => ['https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000002-0000-0000-0000-000000000005', 'name' => 'SSD Samsung 970 EVO 1TB', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 100,
         'images' => ['https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000002-0000-0000-0000-000000000006', 'name' => 'Webcam Logitech C920 Full HD', 'price' => 34900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 40,
         'images' => ['https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000002-0000-0000-0000-000000000007', 'name' => 'HD Externo Seagate 2TB USB 3.0', 'price' => 39900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 70,
         'images' => ['https://images.unsplash.com/photo-1625842268584-8f3296236761?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000002-0000-0000-0000-000000000008', 'name' => 'Roteador Wi-Fi 6 TP-Link AX3000', 'price' => 44900, 'category' => '11111111-0000-0000-0000-000000000002', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 35,
         'images' => ['https://images.unsplash.com/photo-1606904825846-647eb07f5be2?auto=format&fit=crop&w=800&q=80']],

        // ── Games ─────────────────────────────────────────────────────────────
        ['id' => 'aa000003-0000-0000-0000-000000000001', 'name' => 'Controle DualSense PS5 Branco', 'price' => 44900, 'category' => '11111111-0000-0000-0000-000000000003', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 55,
         'images' => ['https://images.unsplash.com/photo-1593118247619-e2d6f056869e?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000003-0000-0000-0000-000000000002', 'name' => 'Headset HyperX Cloud Alpha', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000003', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 40,
         'images' => ['https://images.unsplash.com/photo-1612287230498-43af5a1a9b27?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000003-0000-0000-0000-000000000003', 'name' => 'PlayStation 5 Console', 'price' => 449900, 'category' => '11111111-0000-0000-0000-000000000003', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 8,
         'images' => ['https://images.unsplash.com/photo-1607853202273-797f1c22a38e?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000003-0000-0000-0000-000000000004', 'name' => 'Nintendo Switch OLED', 'price' => 299900, 'category' => '11111111-0000-0000-0000-000000000003', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 22,
         'images' => ['https://images.unsplash.com/photo-1585620385456-4759f9b5c7d9?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000003-0000-0000-0000-000000000005', 'name' => 'Cadeira Gamer DXRacer Formula', 'price' => 199900, 'category' => '11111111-0000-0000-0000-000000000003', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 15,
         'images' => ['https://images.unsplash.com/photo-1598550476439-6847ef35ead3?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000003-0000-0000-0000-000000000006', 'name' => 'Xbox Series X', 'price' => 399900, 'category' => '11111111-0000-0000-0000-000000000003', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 10,
         'images' => ['https://images.unsplash.com/photo-1621259182978-fbf93132d53d?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000003-0000-0000-0000-000000000007', 'name' => 'Mouse Gamer Razer DeathAdder V3', 'price' => 34900, 'category' => '11111111-0000-0000-0000-000000000003', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 65,
         'images' => ['https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=800&q=80']],

        // ── Moda ──────────────────────────────────────────────────────────────
        ['id' => 'aa000004-0000-0000-0000-000000000001', 'name' => 'Camiseta Básica Algodão Premium', 'price' => 4990, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 200,
         'images' => ['https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000004-0000-0000-0000-000000000002', 'name' => 'Vestido Floral Midi', 'price' => 12990, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000004-0000-0000-0000-000000000003', 'name' => 'Calça Jeans Slim Masculina', 'price' => 14990, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 120,
         'images' => ['https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000004-0000-0000-0000-000000000004', 'name' => 'Jaqueta Corta-Vento Impermeável', 'price' => 24990, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 60,
         'images' => ['https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000004-0000-0000-0000-000000000005', 'name' => 'Blusa Tricô Feminina', 'price' => 8990, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 90,
         'images' => ['https://images.unsplash.com/photo-1485231183945-fffde7ea23c7?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000004-0000-0000-0000-000000000006', 'name' => 'Terno Slim Masculino', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 30,
         'images' => ['https://images.unsplash.com/photo-1594938298603-c8148c4b4357?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000004-0000-0000-0000-000000000007', 'name' => 'Casaco Puffer Feminino', 'price' => 19990, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 70,
         'images' => ['https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000004-0000-0000-0000-000000000008', 'name' => 'Shorts Bermuda Moletom', 'price' => 6990, 'category' => '11111111-0000-0000-0000-000000000004', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 150,
         'images' => ['https://images.unsplash.com/photo-1591195853828-11db59a44f43?auto=format&fit=crop&w=800&q=80']],

        // ── Calçados ──────────────────────────────────────────────────────────
        ['id' => 'aa000005-0000-0000-0000-000000000001', 'name' => 'Tênis Nike Air Max 270', 'price' => 79900, 'category' => '11111111-0000-0000-0000-000000000005', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000005-0000-0000-0000-000000000002', 'name' => 'Bota Couro Masculina Chelsea', 'price' => 34990, 'category' => '11111111-0000-0000-0000-000000000005', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 40,
         'images' => ['https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000005-0000-0000-0000-000000000003', 'name' => 'Sandália Birkenstock Arizona', 'price' => 24990, 'category' => '11111111-0000-0000-0000-000000000005', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 60,
         'images' => ['https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000005-0000-0000-0000-000000000004', 'name' => 'Tênis Adidas Stan Smith', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000005', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 55,
         'images' => ['https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000005-0000-0000-0000-000000000005', 'name' => 'Sapatilha Feminina Verniz', 'price' => 12990, 'category' => '11111111-0000-0000-0000-000000000005', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 70,
         'images' => ['https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000005-0000-0000-0000-000000000006', 'name' => 'Tênis de Corrida ASICS Gel', 'price' => 69900, 'category' => '11111111-0000-0000-0000-000000000005', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 45,
         'images' => ['https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000005-0000-0000-0000-000000000007', 'name' => 'Chinelo Havaianas Brasil', 'price' => 3990, 'category' => '11111111-0000-0000-0000-000000000005', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 300,
         'images' => ['https://images.unsplash.com/photo-1603487742131-4160ec999306?auto=format&fit=crop&w=800&q=80']],

        // ── Casa e Decoração ──────────────────────────────────────────────────
        ['id' => 'aa000006-0000-0000-0000-000000000001', 'name' => 'Sofá 3 Lugares Linho Cinza', 'price' => 299900, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 8,
         'images' => ['https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000006-0000-0000-0000-000000000002', 'name' => 'Mesa de Jantar Madeira 6 Lugares', 'price' => 189900, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 12,
         'images' => ['https://images.unsplash.com/photo-1555685812-4b943f1cb0eb?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000006-0000-0000-0000-000000000003', 'name' => 'Luminária de Piso Moderna', 'price' => 29900, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 40,
         'images' => ['https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000006-0000-0000-0000-000000000004', 'name' => 'Quadro Canvas Abstrato 60x80', 'price' => 14990, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 50,
         'images' => ['https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000006-0000-0000-0000-000000000005', 'name' => 'Vaso Cerâmica Decorativo', 'price' => 4990, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 100,
         'images' => ['https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000006-0000-0000-0000-000000000006', 'name' => 'Tapete Sala Shaggy 200x250', 'price' => 49900, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 25,
         'images' => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000006-0000-0000-0000-000000000007', 'name' => 'Espelho Redondo Moldura Dourada', 'price' => 19990, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 35,
         'images' => ['https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000006-0000-0000-0000-000000000008', 'name' => 'Cama Box Queen Size 158cm', 'price' => 249900, 'category' => '11111111-0000-0000-0000-000000000006', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 10,
         'images' => ['https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=800&q=80']],

        // ── Eletrodomésticos ──────────────────────────────────────────────────
        ['id' => 'aa000007-0000-0000-0000-000000000001', 'name' => 'Geladeira Frost Free 480L Brastemp', 'price' => 399900, 'category' => '11111111-0000-0000-0000-000000000007', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 6,
         'images' => ['https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000007-0000-0000-0000-000000000002', 'name' => 'Máquina de Lavar 12kg Electrolux', 'price' => 249900, 'category' => '11111111-0000-0000-0000-000000000007', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 9,
         'images' => ['https://images.unsplash.com/photo-1626806787461-102c1a7f1b9d?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000007-0000-0000-0000-000000000003', 'name' => 'Micro-ondas Electrolux 31L', 'price' => 69900, 'category' => '11111111-0000-0000-0000-000000000007', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 20,
         'images' => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000007-0000-0000-0000-000000000004', 'name' => 'Cafeteira Nespresso Vertuo', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000007', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 30,
         'images' => ['https://images.unsplash.com/photo-1510017803434-a899398421b3?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000007-0000-0000-0000-000000000005', 'name' => 'Liquidificador Oster 1000W', 'price' => 19900, 'category' => '11111111-0000-0000-0000-000000000007', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 45,
         'images' => ['https://images.unsplash.com/photo-1570222094114-d054a817e56b?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000007-0000-0000-0000-000000000006', 'name' => 'Aspirador de Pó Dyson V15', 'price' => 349900, 'category' => '11111111-0000-0000-0000-000000000007', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 15,
         'images' => ['https://images.unsplash.com/photo-1558317374-067fb5f30001?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000007-0000-0000-0000-000000000007', 'name' => 'Ar Condicionado Split 12000BTU Inverter', 'price' => 249900, 'category' => '11111111-0000-0000-0000-000000000007', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 12,
         'images' => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?auto=format&fit=crop&w=800&q=80']],

        // ── Esportes e Lazer ──────────────────────────────────────────────────
        ['id' => 'aa000008-0000-0000-0000-000000000001', 'name' => 'Bicicleta MTB Aro 29 Shimano', 'price' => 299900, 'category' => '11111111-0000-0000-0000-000000000008', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 10,
         'images' => ['https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000008-0000-0000-0000-000000000002', 'name' => 'Esteira Ergométrica 110V', 'price' => 399900, 'category' => '11111111-0000-0000-0000-000000000008', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 7,
         'images' => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000008-0000-0000-0000-000000000003', 'name' => 'Halteres 5kg Par Emborrachado', 'price' => 9990, 'category' => '11111111-0000-0000-0000-000000000008', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 120,
         'images' => ['https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000008-0000-0000-0000-000000000004', 'name' => 'Tênis de Futebol Society Nike Phantom', 'price' => 49900, 'category' => '11111111-0000-0000-0000-000000000008', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 50,
         'images' => ['https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000008-0000-0000-0000-000000000005', 'name' => 'Raquete de Tênis Wilson Pro', 'price' => 39900, 'category' => '11111111-0000-0000-0000-000000000008', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 35,
         'images' => ['https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000008-0000-0000-0000-000000000006', 'name' => 'Tapete de Yoga Antiderrapante 6mm', 'price' => 9990, 'category' => '11111111-0000-0000-0000-000000000008', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 90,
         'images' => ['https://images.unsplash.com/photo-1575052814086-f385e2e2ad1b?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000008-0000-0000-0000-000000000007', 'name' => 'Prancha de Surf 7\'0 Longboard', 'price' => 149900, 'category' => '11111111-0000-0000-0000-000000000008', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 6,
         'images' => ['https://images.unsplash.com/photo-1531722569936-825d4ecc6827?auto=format&fit=crop&w=800&q=80']],

        // ── Beleza e Saúde ────────────────────────────────────────────────────
        ['id' => 'aa000009-0000-0000-0000-000000000001', 'name' => 'Perfume Chanel Nº5 100ml EDP', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000009', 'company' => 'ef2ce8c2-d777-4aaf-a5de-ed34d93287ed', 'stock' => 40,
         'images' => ['https://images.unsplash.com/photo-1541643600914-78b084683702?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000009-0000-0000-0000-000000000002', 'name' => 'Secador de Cabelo Dyson Supersonic', 'price' => 249900, 'category' => '11111111-0000-0000-0000-000000000009', 'company' => 'ef2ce8c2-d777-4aaf-a5de-ed34d93287ed', 'stock' => 15,
         'images' => ['https://images.unsplash.com/photo-1522338242992-e1a54906a8da?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000009-0000-0000-0000-000000000003', 'name' => 'Kit Skincare Hidratante Neutrogena', 'price' => 14990, 'category' => '11111111-0000-0000-0000-000000000009', 'company' => 'ef2ce8c2-d777-4aaf-a5de-ed34d93287ed', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000009-0000-0000-0000-000000000004', 'name' => 'Chapinha GHD Platinum+', 'price' => 189900, 'category' => '11111111-0000-0000-0000-000000000009', 'company' => 'ef2ce8c2-d777-4aaf-a5de-ed34d93287ed', 'stock' => 20,
         'images' => ['https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000009-0000-0000-0000-000000000005', 'name' => 'Protetor Solar FPS 70 La Roche-Posay', 'price' => 8990, 'category' => '11111111-0000-0000-0000-000000000009', 'company' => 'ef2ce8c2-d777-4aaf-a5de-ed34d93287ed', 'stock' => 150,
         'images' => ['https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000009-0000-0000-0000-000000000006', 'name' => 'Paleta de Sombras Nude NYX', 'price' => 7990, 'category' => '11111111-0000-0000-0000-000000000009', 'company' => 'ef2ce8c2-d777-4aaf-a5de-ed34d93287ed', 'stock' => 60,
         'images' => ['https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000009-0000-0000-0000-000000000007', 'name' => 'Escova Elétrica Oral-B iO Series 9', 'price' => 79900, 'category' => '11111111-0000-0000-0000-000000000009', 'company' => 'ef2ce8c2-d777-4aaf-a5de-ed34d93287ed', 'stock' => 35,
         'images' => ['https://images.unsplash.com/photo-1607613009820-a29f7bb81c04?auto=format&fit=crop&w=800&q=80']],

        // ── Alimentos e Bebidas ───────────────────────────────────────────────
        ['id' => 'aa000010-0000-0000-0000-000000000001', 'name' => 'Café Especial Arábica 500g Moído', 'price' => 4990, 'category' => '11111111-0000-0000-0000-000000000010', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 200,
         'images' => ['https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000010-0000-0000-0000-000000000002', 'name' => 'Kit Chá Artesanal 12 Sabores', 'price' => 6990, 'category' => '11111111-0000-0000-0000-000000000010', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 100,
         'images' => ['https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000010-0000-0000-0000-000000000003', 'name' => 'Azeite Extra Virgem Gallo 500ml', 'price' => 3990, 'category' => '11111111-0000-0000-0000-000000000010', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 150,
         'images' => ['https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000010-0000-0000-0000-000000000004', 'name' => 'Whey Protein Gold Standard 2kg', 'price' => 18990, 'category' => '11111111-0000-0000-0000-000000000010', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1594498653385-d5172c532c00?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000010-0000-0000-0000-000000000005', 'name' => 'Chocolate Lindt 70% Cacau 100g', 'price' => 1990, 'category' => '11111111-0000-0000-0000-000000000010', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 300,
         'images' => ['https://images.unsplash.com/photo-1511381939415-e44015466834?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000010-0000-0000-0000-000000000006', 'name' => 'Granola Orgânica Sem Glúten 400g', 'price' => 3490, 'category' => '11111111-0000-0000-0000-000000000010', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 120,
         'images' => ['https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000010-0000-0000-0000-000000000007', 'name' => 'Vinho Tinto Argentino Malbec 750ml', 'price' => 8990, 'category' => '11111111-0000-0000-0000-000000000010', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 90,
         'images' => ['https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=800&q=80']],

        // ── Livros e Papelaria ────────────────────────────────────────────────
        ['id' => 'aa000011-0000-0000-0000-000000000001', 'name' => 'Clean Code - Robert C. Martin', 'price' => 9990, 'category' => '11111111-0000-0000-0000-000000000011', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 60,
         'images' => ['https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000011-0000-0000-0000-000000000002', 'name' => 'Caderno Leuchtturm1917 A5 Pautado', 'price' => 9990, 'category' => '11111111-0000-0000-0000-000000000011', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1517842645767-c639042777db?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000011-0000-0000-0000-000000000003', 'name' => 'Caneta Montblanc Starwalker', 'price' => 129900, 'category' => '11111111-0000-0000-0000-000000000011', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 15,
         'images' => ['https://images.unsplash.com/photo-1585336261022-680e295ce3fe?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000011-0000-0000-0000-000000000004', 'name' => 'The Pragmatic Programmer', 'price' => 12990, 'category' => '11111111-0000-0000-0000-000000000011', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 40,
         'images' => ['https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000011-0000-0000-0000-000000000005', 'name' => 'Post-it Notes 3M Multicolor 12un', 'price' => 2990, 'category' => '11111111-0000-0000-0000-000000000011', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 200,
         'images' => ['https://images.unsplash.com/photo-1586281380349-632531db7ed4?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000011-0000-0000-0000-000000000006', 'name' => 'Kindle Paperwhite 11ª Geração', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000011', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 30,
         'images' => ['https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000011-0000-0000-0000-000000000007', 'name' => 'Atomic Habits - James Clear', 'price' => 6990, 'category' => '11111111-0000-0000-0000-000000000011', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 70,
         'images' => ['https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=800&q=80']],

        // ── Brinquedos ────────────────────────────────────────────────────────
        ['id' => 'aa000012-0000-0000-0000-000000000001', 'name' => 'LEGO Technic Land Rover Defender', 'price' => 99900, 'category' => '11111111-0000-0000-0000-000000000012', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 20,
         'images' => ['https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000012-0000-0000-0000-000000000002', 'name' => 'Boneca Barbie Fashionista 2024', 'price' => 7990, 'category' => '11111111-0000-0000-0000-000000000012', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000012-0000-0000-0000-000000000003', 'name' => 'Carrinho Hot Wheels Pista Looping', 'price' => 14990, 'category' => '11111111-0000-0000-0000-000000000012', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 45,
         'images' => ['https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000012-0000-0000-0000-000000000004', 'name' => 'Pelúcia Urso Teddy 50cm', 'price' => 5990, 'category' => '11111111-0000-0000-0000-000000000012', 'company' => '1595b318-57e3-4246-9a71-ec8d7dd62e62', 'stock' => 100,
         'images' => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000012-0000-0000-0000-000000000005', 'name' => 'Drone DJI Mini 4 Pro', 'price' => 399900, 'category' => '11111111-0000-0000-0000-000000000012', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 8,
         'images' => ['https://images.unsplash.com/photo-1579829366248-204fe8413f31?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000012-0000-0000-0000-000000000006', 'name' => 'Quebra-Cabeça 1000 Peças', 'price' => 5990, 'category' => '11111111-0000-0000-0000-000000000012', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 60,
         'images' => ['https://images.unsplash.com/photo-1611996575749-79a3a250f948?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000012-0000-0000-0000-000000000007', 'name' => 'Jogo de Tabuleiro Banco Imobiliário', 'price' => 9990, 'category' => '11111111-0000-0000-0000-000000000012', 'company' => '61304e32-bf1d-4ac0-90f3-5532c825f6cc', 'stock' => 50,
         'images' => ['https://images.unsplash.com/photo-1610890716171-6b1bb98ffd09?auto=format&fit=crop&w=800&q=80']],

        // ── Automotivo ────────────────────────────────────────────────────────
        ['id' => 'aa000013-0000-0000-0000-000000000001', 'name' => 'Pneu Michelin 205/55 R16', 'price' => 59900, 'category' => '11111111-0000-0000-0000-000000000013', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 30,
         'images' => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000013-0000-0000-0000-000000000002', 'name' => 'Suporte Veicular para Smartphone', 'price' => 4990, 'category' => '11111111-0000-0000-0000-000000000013', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 150,
         'images' => ['https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000013-0000-0000-0000-000000000003', 'name' => 'Central Multimídia Pioneer 7"', 'price' => 149900, 'category' => '11111111-0000-0000-0000-000000000013', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 20,
         'images' => ['https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000013-0000-0000-0000-000000000004', 'name' => 'Câmera de Ré com Tela 4.3"', 'price' => 19990, 'category' => '11111111-0000-0000-0000-000000000013', 'company' => '07e8a754-1893-437c-9d14-389c14a28d5c', 'stock' => 35,
         'images' => ['https://images.unsplash.com/photo-1592194996308-7b43878e84a6?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000013-0000-0000-0000-000000000005', 'name' => 'Capa de Banco Automotivo Couro', 'price' => 24990, 'category' => '11111111-0000-0000-0000-000000000013', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 40,
         'images' => ['https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000013-0000-0000-0000-000000000006', 'name' => 'Carregador Veicular USB-C 65W', 'price' => 5990, 'category' => '11111111-0000-0000-0000-000000000013', 'company' => '3208ecd5-a5d0-45aa-bf1b-78d6a93e9402', 'stock' => 100,
         'images' => ['https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000013-0000-0000-0000-000000000007', 'name' => 'Kit Limpeza Automotiva Premium', 'price' => 9990, 'category' => '11111111-0000-0000-0000-000000000013', 'company' => 'aca57700-7aa9-45d4-93c3-8d7aa8ef3875', 'stock' => 70,
         'images' => ['https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=800&q=80']],

        // ── Ferramentas ───────────────────────────────────────────────────────
        ['id' => 'aa000014-0000-0000-0000-000000000001', 'name' => 'Furadeira de Impacto Bosch 650W', 'price' => 29990, 'category' => '11111111-0000-0000-0000-000000000014', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 25,
         'images' => ['https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000014-0000-0000-0000-000000000002', 'name' => 'Parafusadeira a Bateria DeWalt 18V', 'price' => 49990, 'category' => '11111111-0000-0000-0000-000000000014', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 18,
         'images' => ['https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000014-0000-0000-0000-000000000003', 'name' => 'Jogo de Chaves de Fenda 20 Peças', 'price' => 4990, 'category' => '11111111-0000-0000-0000-000000000014', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1581166397057-235af2b3c6dd?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000014-0000-0000-0000-000000000004', 'name' => 'Nível a Laser Bosch GLL 3-80', 'price' => 89990, 'category' => '11111111-0000-0000-0000-000000000014', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 12,
         'images' => ['https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000014-0000-0000-0000-000000000005', 'name' => 'Caixa de Ferramentas Tramontina 22"', 'price' => 19990, 'category' => '11111111-0000-0000-0000-000000000014', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 30,
         'images' => ['https://images.unsplash.com/photo-1530124566582-a618bc2615dc?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000014-0000-0000-0000-000000000006', 'name' => 'Serra Circular 7 1/4 Makita', 'price' => 59990, 'category' => '11111111-0000-0000-0000-000000000014', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 10,
         'images' => ['https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000014-0000-0000-0000-000000000007', 'name' => 'Fita Métrica 5m Stanley', 'price' => 1990, 'category' => '11111111-0000-0000-0000-000000000014', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 200,
         'images' => ['https://images.unsplash.com/photo-1581166397057-235af2b3c6dd?auto=format&fit=crop&w=800&q=80']],

        // ── Animais de Estimação ──────────────────────────────────────────────
        ['id' => 'aa000015-0000-0000-0000-000000000001', 'name' => 'Ração Royal Canin Adulto 15kg', 'price' => 29990, 'category' => '11111111-0000-0000-0000-000000000015', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 50,
         'images' => ['https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000015-0000-0000-0000-000000000002', 'name' => 'Arranhador para Gatos Torre 70cm', 'price' => 12990, 'category' => '11111111-0000-0000-0000-000000000015', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 30,
         'images' => ['https://images.unsplash.com/photo-1533743983669-94fa5c4338ec?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000015-0000-0000-0000-000000000003', 'name' => 'Coleira Antipulgas Seresto', 'price' => 9990, 'category' => '11111111-0000-0000-0000-000000000015', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 80,
         'images' => ['https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000015-0000-0000-0000-000000000004', 'name' => 'Cama para Cachorro Ortopédica L', 'price' => 14990, 'category' => '11111111-0000-0000-0000-000000000015', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 25,
         'images' => ['https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000015-0000-0000-0000-000000000005', 'name' => 'Aquário 120L Kit Completo', 'price' => 49990, 'category' => '11111111-0000-0000-0000-000000000015', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 8,
         'images' => ['https://images.unsplash.com/photo-1522069169874-c58ec4b76be5?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000015-0000-0000-0000-000000000006', 'name' => 'Brinquedo Interativo Gato Kong', 'price' => 3990, 'category' => '11111111-0000-0000-0000-000000000015', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 100,
         'images' => ['https://images.unsplash.com/photo-1533743983669-94fa5c4338ec?auto=format&fit=crop&w=800&q=80']],
        ['id' => 'aa000015-0000-0000-0000-000000000007', 'name' => 'Transportadora Pet Airline M', 'price' => 19990, 'category' => '11111111-0000-0000-0000-000000000015', 'company' => '914a8118-7f31-4af2-94e4-e9dd4a8a3126', 'stock' => 20,
         'images' => ['https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=800&q=80']],
    ];

    public function run(): void
    {
        $now = now()->toDateTimeString();

        foreach (self::PRODUCTS as $product) {
            $images = $product['images'];

            // Compute a deterministic slug — same algorithm used in ProductModel::booted() and slug migration
            $slugBase   = Str::slug($product['name']);
            $slugSuffix = substr(str_replace('-', '', $product['id']), 0, 6);
            $slug       = "{$slugBase}-{$slugSuffix}";

            // Upsert product
            DB::table('products')->upsert([
                'id'             => $product['id'],
                'name'           => $product['name'],
                'slug'           => $slug,
                'price_amount'   => $product['price'],
                'price_currency' => 'BRL',
                'category_id'    => $product['category'],
                'company_id'     => $product['company'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ], ['id'], ['name', 'slug', 'price_amount', 'category_id', 'company_id', 'updated_at']);

            // Upsert stock — deterministic ID derived from product id
            $stockId = Str::uuid()->toString();
            // Use a deterministic UUID via md5 hash for idempotency
            $stockId = sprintf(
                '%08s-%04s-%04s-%04s-%12s',
                substr(md5('stock-' . $product['id']), 0, 8),
                substr(md5('stock-' . $product['id']), 8, 4),
                substr(md5('stock-' . $product['id']), 12, 4),
                substr(md5('stock-' . $product['id']), 16, 4),
                substr(md5('stock-' . $product['id']), 20, 12)
            );
            DB::table('stocks')->upsert([
                'id'                => $stockId,
                'product_id'        => $product['id'],
                'quantity_total'    => $product['stock'],
                'quantity_reserved' => 0,
                'created_at'        => $now,
                'updated_at'        => $now,
            ], ['product_id'], ['quantity_total', 'updated_at']);

            // Upsert gallery
            foreach ($images as $position => $url) {
                $galleryId = sprintf(
                    '%08s-%04s-%04s-%04s-%12s',
                    substr(md5('gallery-' . $product['id'] . '-' . $position), 0, 8),
                    substr(md5('gallery-' . $product['id'] . '-' . $position), 8, 4),
                    substr(md5('gallery-' . $product['id'] . '-' . $position), 12, 4),
                    substr(md5('gallery-' . $product['id'] . '-' . $position), 16, 4),
                    substr(md5('gallery-' . $product['id'] . '-' . $position), 20, 12)
                );
                DB::table('product_gallery')->upsert([
                    'id'         => $galleryId,
                    'product_id' => $product['id'],
                    'url'        => $url,
                    'position'   => $position,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], ['id'], ['url', 'updated_at']);
            }
        }

        $this->command->info('✔ ' . count(self::PRODUCTS) . ' produtos inseridos/atualizados com imagens e estoque.');
    }
}
