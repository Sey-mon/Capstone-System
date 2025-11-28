<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodsTableSeeder extends Seeder
{
    /**
     * Seed the foods table with common Filipino foods for meal planning
     * Can be run multiple times - will skip existing foods
     */
    public function run()
    {
        // Clear existing foods if you want to start fresh (optional - comment out if you want to keep existing)
        // DB::table('foods')->truncate();
        
        $foods = [
            // GRAINS & CARBOHYDRATES
            ['food_name_and_description' => 'Kanin (white rice)', 'alternate_common_names' => 'bigas, white rice, steamed rice', 'energy_kcal' => 130, 'nutrition_tags' => 'carbohydrate, energy, staple food'],
            ['food_name_and_description' => 'Sinangag (fried rice)', 'alternate_common_names' => 'garlic fried rice, fried rice', 'energy_kcal' => 180, 'nutrition_tags' => 'carbohydrate, energy'],
            ['food_name_and_description' => 'Yang chow fried rice', 'alternate_common_names' => 'special fried rice', 'energy_kcal' => 210, 'nutrition_tags' => 'carbohydrate, energy'],
            ['food_name_and_description' => 'Lugaw (rice porridge)', 'alternate_common_names' => 'congee, rice porridge, arroz caldo', 'energy_kcal' => 90, 'nutrition_tags' => 'soft food, easy to digest, for babies'],
            ['food_name_and_description' => 'Mais (corn)', 'alternate_common_names' => 'corn, sweet corn', 'energy_kcal' => 86, 'nutrition_tags' => 'carbohydrate, fiber, vitamin C'],
            ['food_name_and_description' => 'Mais rice (corn rice)', 'alternate_common_names' => 'corn grits, cornmeal', 'energy_kcal' => 96, 'nutrition_tags' => 'carbohydrate, fiber, gluten-free'],
            ['food_name_and_description' => 'Nilagang mais (boiled corn)', 'alternate_common_names' => 'boiled sweet corn', 'energy_kcal' => 86, 'nutrition_tags' => 'carbohydrate, fiber, snack'],
            ['food_name_and_description' => 'Pandesal (bread roll)', 'alternate_common_names' => 'Filipino bread, bread roll', 'energy_kcal' => 138, 'nutrition_tags' => 'carbohydrate, breakfast food'],
            ['food_name_and_description' => 'Tasty bread (loaf bread)', 'alternate_common_names' => 'white bread, sliced bread', 'energy_kcal' => 265, 'nutrition_tags' => 'carbohydrate, bread'],
            ['food_name_and_description' => 'Wheat bread (whole wheat)', 'alternate_common_names' => 'brown bread, whole grain bread', 'energy_kcal' => 247, 'nutrition_tags' => 'carbohydrate, fiber, whole grain'],
            ['food_name_and_description' => 'Monay (Filipino bread)', 'alternate_common_names' => 'Filipino bread roll', 'energy_kcal' => 150, 'nutrition_tags' => 'carbohydrate, bread'],
            ['food_name_and_description' => 'Putok (Filipino bread)', 'alternate_common_names' => 'crunchy bread top', 'energy_kcal' => 145, 'nutrition_tags' => 'carbohydrate, bread'],
            ['food_name_and_description' => 'Pancit canton (stir-fried noodles)', 'alternate_common_names' => 'noodles, canton', 'energy_kcal' => 180, 'nutrition_tags' => 'carbohydrate, traditional dish'],
            ['food_name_and_description' => 'Pancit bihon (rice noodles)', 'alternate_common_names' => 'rice noodles, bihon', 'energy_kcal' => 192, 'nutrition_tags' => 'carbohydrate, gluten-free'],
            ['food_name_and_description' => 'Pancit sotanghon (glass noodles)', 'alternate_common_names' => 'vermicelli, bean thread noodles', 'energy_kcal' => 181, 'nutrition_tags' => 'carbohydrate, gluten-free'],
            ['food_name_and_description' => 'Misua (thin wheat noodles)', 'alternate_common_names' => 'flour vermicelli, miswa', 'energy_kcal' => 178, 'nutrition_tags' => 'carbohydrate, noodles'],
            ['food_name_and_description' => 'Miki noodles (fresh egg noodles)', 'alternate_common_names' => 'miki, fresh noodles', 'energy_kcal' => 138, 'nutrition_tags' => 'carbohydrate, noodles'],
            ['food_name_and_description' => 'Lomi noodles (thick fresh noodles)', 'alternate_common_names' => 'lomi, thick noodles', 'energy_kcal' => 147, 'nutrition_tags' => 'carbohydrate, noodles'],
            ['food_name_and_description' => 'Oatmeal', 'alternate_common_names' => 'oats, rolled oats', 'energy_kcal' => 68, 'nutrition_tags' => 'carbohydrate, fiber, whole grain'],
            ['food_name_and_description' => 'Quinoa', 'alternate_common_names' => 'quinoa grain', 'energy_kcal' => 120, 'nutrition_tags' => 'carbohydrate, protein, fiber, whole grain'],
            ['food_name_and_description' => 'Pasta (spaghetti)', 'alternate_common_names' => 'spaghetti noodles, pasta', 'energy_kcal' => 158, 'nutrition_tags' => 'carbohydrate, energy'],
            ['food_name_and_description' => 'Macaroni (elbow pasta)', 'alternate_common_names' => 'elbow macaroni, pasta', 'energy_kcal' => 158, 'nutrition_tags' => 'carbohydrate, energy'],
            ['food_name_and_description' => 'Cassava (kamoteng kahoy)', 'alternate_common_names' => 'cassava root, yuca', 'energy_kcal' => 160, 'nutrition_tags' => 'carbohydrate, gluten-free'],
            ['food_name_and_description' => 'Ube (purple yam)', 'alternate_common_names' => 'purple yam, ubi', 'energy_kcal' => 140, 'nutrition_tags' => 'carbohydrate, fiber, traditional food'],
            ['food_name_and_description' => 'Gabi (taro root)', 'alternate_common_names' => 'taro, dasheen', 'energy_kcal' => 112, 'nutrition_tags' => 'carbohydrate, fiber'],
            
            // PROTEINS - CHICKEN
            ['food_name_and_description' => 'Manok (chicken meat)', 'alternate_common_names' => 'chicken, chicken breast, chicken thigh', 'energy_kcal' => 165, 'nutrition_tags' => 'protein, lean meat, zinc, vitamin B'],
            ['food_name_and_description' => 'Pritong manok (fried chicken)', 'alternate_common_names' => 'fried chicken, crispy chicken', 'energy_kcal' => 280, 'nutrition_tags' => 'protein, zinc, vitamin B'],
            ['food_name_and_description' => 'Inihaw na manok (grilled chicken)', 'alternate_common_names' => 'grilled chicken, barbecue chicken', 'energy_kcal' => 195, 'nutrition_tags' => 'protein, lean meat, zinc'],
            ['food_name_and_description' => 'Chicken breast (pechuga)', 'alternate_common_names' => 'chicken breast fillet', 'energy_kcal' => 165, 'nutrition_tags' => 'protein, lean, low fat'],
            ['food_name_and_description' => 'Chicken thigh (hita ng manok)', 'alternate_common_names' => 'chicken leg, chicken thigh', 'energy_kcal' => 209, 'nutrition_tags' => 'protein, iron, zinc'],
            ['food_name_and_description' => 'Tinola (chicken ginger soup)', 'alternate_common_names' => 'chicken soup, ginger chicken soup', 'energy_kcal' => 120, 'nutrition_tags' => 'protein, traditional dish, soup'],
            ['food_name_and_description' => 'Adobong manok (chicken adobo)', 'alternate_common_names' => 'chicken adobo, adobo', 'energy_kcal' => 210, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Chicken curry (Filipino-style)', 'alternate_common_names' => 'chicken curry', 'energy_kcal' => 230, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Chicken asado (sweet chicken)', 'alternate_common_names' => 'asadong manok', 'energy_kcal' => 220, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Arroz caldo (chicken rice porridge)', 'alternate_common_names' => 'chicken porridge, lugaw na manok', 'energy_kcal' => 150, 'nutrition_tags' => 'protein, soft food, easy to digest'],
            ['food_name_and_description' => 'Chicken inasal (grilled marinated chicken)', 'alternate_common_names' => 'Bacolod chicken, inasal', 'energy_kcal' => 205, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Chicken afritada (chicken tomato stew)', 'alternate_common_names' => 'afritadang manok', 'energy_kcal' => 215, 'nutrition_tags' => 'protein, traditional dish'],
            
            // PROTEINS - PORK
            ['food_name_and_description' => 'Baboy (pork meat)', 'alternate_common_names' => 'pork, pork loin, pork chop', 'energy_kcal' => 242, 'nutrition_tags' => 'protein, iron, vitamin B12'],
            ['food_name_and_description' => 'Pork chop (chuleta)', 'alternate_common_names' => 'pork chop, chuleta', 'energy_kcal' => 231, 'nutrition_tags' => 'protein, iron, vitamin B12'],
            ['food_name_and_description' => 'Pork loin (lomo)', 'alternate_common_names' => 'pork tenderloin', 'energy_kcal' => 206, 'nutrition_tags' => 'protein, lean, vitamin B12'],
            ['food_name_and_description' => 'Pork belly (liempo)', 'alternate_common_names' => 'liempo, belly pork', 'energy_kcal' => 518, 'nutrition_tags' => 'protein, fats, vitamin B12'],
            ['food_name_and_description' => 'Inihaw na liempo (grilled pork belly)', 'alternate_common_names' => 'grilled liempo, BBQ pork', 'energy_kcal' => 480, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Adobong baboy (pork adobo)', 'alternate_common_names' => 'pork adobo', 'energy_kcal' => 280, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Sinigang na baboy (pork sour soup)', 'alternate_common_names' => 'pork sinigang, sour soup', 'energy_kcal' => 190, 'nutrition_tags' => 'protein, traditional dish, soup, vitamin C'],
            ['food_name_and_description' => 'Nilagang baboy (boiled pork)', 'alternate_common_names' => 'boiled pork, pork stew', 'energy_kcal' => 220, 'nutrition_tags' => 'protein, soup'],
            ['food_name_and_description' => 'Menudo (pork and liver stew)', 'alternate_common_names' => 'pork stew', 'energy_kcal' => 250, 'nutrition_tags' => 'protein, iron, traditional dish'],
            ['food_name_and_description' => 'Pork steak (bistek baboy)', 'alternate_common_names' => 'pork bistek', 'energy_kcal' => 270, 'nutrition_tags' => 'protein, iron, traditional dish'],
            ['food_name_and_description' => 'Pork barbecue (inihaw na baboy)', 'alternate_common_names' => 'BBQ pork skewers', 'energy_kcal' => 290, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Pork giniling (ground pork)', 'alternate_common_names' => 'minced pork', 'energy_kcal' => 263, 'nutrition_tags' => 'protein, iron, vitamin B12'],
            ['food_name_and_description' => 'Giniling na baboy (sauteed ground pork)', 'alternate_common_names' => 'pork picadillo', 'energy_kcal' => 285, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Lechon (roasted pig)', 'alternate_common_names' => 'roasted pork, whole roasted pig', 'energy_kcal' => 450, 'nutrition_tags' => 'protein, traditional dish, festive food'],
            ['food_name_and_description' => 'Pork hamonado (sweetened pork)', 'alternate_common_names' => 'hamonado', 'energy_kcal' => 295, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Bagnet (crispy pork)', 'alternate_common_names' => 'Ilocano crispy pork', 'energy_kcal' => 485, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Pata tim (sweet braised pork leg)', 'alternate_common_names' => 'braised pork hock', 'energy_kcal' => 320, 'nutrition_tags' => 'protein, traditional dish'],
            
            // PROTEINS - FISH & SEAFOOD
            ['food_name_and_description' => 'Tilapia (tilapia fish)', 'alternate_common_names' => 'fish, white fish', 'energy_kcal' => 128, 'nutrition_tags' => 'protein, omega-3, lean'],
            ['food_name_and_description' => 'Bangus (milkfish)', 'alternate_common_names' => 'milkfish, fish', 'energy_kcal' => 142, 'nutrition_tags' => 'protein, omega-3, calcium'],
            ['food_name_and_description' => 'Galunggong (round scad)', 'alternate_common_names' => 'mackerel scad, fish', 'energy_kcal' => 135, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Pritong isda (fried fish)', 'alternate_common_names' => 'fried fish, crispy fish', 'energy_kcal' => 180, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Sinigang na isda (fish sour soup)', 'alternate_common_names' => 'fish sinigang, sour fish soup', 'energy_kcal' => 140, 'nutrition_tags' => 'protein, traditional dish, vitamin C'],
            ['food_name_and_description' => 'Hipon (shrimp)', 'alternate_common_names' => 'shrimp, prawns', 'energy_kcal' => 99, 'nutrition_tags' => 'protein, low fat, selenium'],
            ['food_name_and_description' => 'Ginataang hipon (shrimp in coconut milk)', 'alternate_common_names' => 'shrimp coconut curry', 'energy_kcal' => 180, 'nutrition_tags' => 'protein, traditional dish'],
            
            // MORE SPECIFIC FILIPINO FISH & SEAFOOD
            ['food_name_and_description' => 'Pritong tilapia (fried tilapia)', 'alternate_common_names' => 'fried tilapia, crispy tilapia', 'energy_kcal' => 180, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Pritong bangus (fried milkfish)', 'alternate_common_names' => 'fried bangus, daing na bangus', 'energy_kcal' => 195, 'nutrition_tags' => 'protein, omega-3, calcium'],
            ['food_name_and_description' => 'Rellenong bangus (stuffed milkfish)', 'alternate_common_names' => 'stuffed bangus', 'energy_kcal' => 220, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Sinigang na bangus (milkfish sour soup)', 'alternate_common_names' => 'bangus sinigang', 'energy_kcal' => 145, 'nutrition_tags' => 'protein, traditional dish, vitamin C'],
            ['food_name_and_description' => 'Pritong galunggong (fried round scad)', 'alternate_common_names' => 'fried galunggong', 'energy_kcal' => 185, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Tulingan (skipjack tuna)', 'alternate_common_names' => 'skipjack, mackerel tuna', 'energy_kcal' => 144, 'nutrition_tags' => 'protein, omega-3, iron'],
            ['food_name_and_description' => 'Pritong tulingan (fried skipjack)', 'alternate_common_names' => 'fried tulingan', 'energy_kcal' => 190, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Bariles/Alumahan (mackerel)', 'alternate_common_names' => 'mackerel, tangigue', 'energy_kcal' => 139, 'nutrition_tags' => 'protein, omega-3, vitamin B12'],
            ['food_name_and_description' => 'Lapu-lapu (grouper)', 'alternate_common_names' => 'grouper, pugapo', 'energy_kcal' => 118, 'nutrition_tags' => 'protein, lean, omega-3'],
            ['food_name_and_description' => 'Inihaw na lapu-lapu (grilled grouper)', 'alternate_common_names' => 'grilled lapu-lapu', 'energy_kcal' => 140, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Maya-maya (red snapper)', 'alternate_common_names' => 'red snapper, snapper', 'energy_kcal' => 128, 'nutrition_tags' => 'protein, omega-3, lean'],
            ['food_name_and_description' => 'Inihaw na maya-maya (grilled red snapper)', 'alternate_common_names' => 'grilled maya-maya', 'energy_kcal' => 145, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Dalagang bukid (yellow tail fusilier)', 'alternate_common_names' => 'yellow tail fish', 'energy_kcal' => 132, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Espada (swordfish)', 'alternate_common_names' => 'swordfish, billfish', 'energy_kcal' => 144, 'nutrition_tags' => 'protein, omega-3, selenium'],
            ['food_name_and_description' => 'Tangigue (Spanish mackerel)', 'alternate_common_names' => 'Spanish mackerel, kingfish', 'energy_kcal' => 158, 'nutrition_tags' => 'protein, omega-3, vitamin B12'],
            ['food_name_and_description' => 'Inihaw na tangigue (grilled Spanish mackerel)', 'alternate_common_names' => 'grilled tangigue', 'energy_kcal' => 175, 'nutrition_tags' => 'protein, omega-3'],
            ['food_name_and_description' => 'Tuna (canned/fresh)', 'alternate_common_names' => 'tuna, canned tuna', 'energy_kcal' => 132, 'nutrition_tags' => 'protein, omega-3, vitamin D'],
            ['food_name_and_description' => 'Salmon (imported)', 'alternate_common_names' => 'salmon fillet', 'energy_kcal' => 208, 'nutrition_tags' => 'protein, omega-3, vitamin D'],
            ['food_name_and_description' => 'Pusit (squid)', 'alternate_common_names' => 'squid, calamari', 'energy_kcal' => 92, 'nutrition_tags' => 'protein, low fat, iron'],
            ['food_name_and_description' => 'Adobong pusit (squid adobo)', 'alternate_common_names' => 'squid in ink, pusit adobo', 'energy_kcal' => 140, 'nutrition_tags' => 'protein, traditional dish, iron'],
            ['food_name_and_description' => 'Sugpo (prawn)', 'alternate_common_names' => 'tiger prawn, large shrimp', 'energy_kcal' => 106, 'nutrition_tags' => 'protein, low fat, selenium'],
            ['food_name_and_description' => 'Talaba (oyster)', 'alternate_common_names' => 'oyster, fresh oyster', 'energy_kcal' => 68, 'nutrition_tags' => 'protein, zinc, iron, omega-3'],
            ['food_name_and_description' => 'Tahong (green mussel)', 'alternate_common_names' => 'mussels, green mussels', 'energy_kcal' => 86, 'nutrition_tags' => 'protein, iron, omega-3'],
            ['food_name_and_description' => 'Halaan (clams)', 'alternate_common_names' => 'clams, shellfish', 'energy_kcal' => 74, 'nutrition_tags' => 'protein, iron, vitamin B12'],
            ['food_name_and_description' => 'Ginisang halaan (sauteed clams)', 'alternate_common_names' => 'clams in ginger sauce', 'energy_kcal' => 95, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Alimango (crab)', 'alternate_common_names' => 'mud crab, crab', 'energy_kcal' => 97, 'nutrition_tags' => 'protein, low fat, selenium'],
            ['food_name_and_description' => 'Ginataang alimango (crab in coconut)', 'alternate_common_names' => 'crab coconut curry', 'energy_kcal' => 165, 'nutrition_tags' => 'protein, traditional dish'],
            
            // PROTEINS - EGGS & BEEF
            ['food_name_and_description' => 'Itlog (chicken egg)', 'alternate_common_names' => 'egg, boiled egg, fried egg', 'energy_kcal' => 155, 'nutrition_tags' => 'protein, vitamin D, choline, iron'],
            ['food_name_and_description' => 'Itlog na pula (salted egg)', 'alternate_common_names' => 'salted duck egg, itlog na maalat', 'energy_kcal' => 137, 'nutrition_tags' => 'protein, vitamin D, sodium'],
            ['food_name_and_description' => 'Scrambled eggs (pritong itlog)', 'alternate_common_names' => 'scrambled egg', 'energy_kcal' => 168, 'nutrition_tags' => 'protein, breakfast food'],
            ['food_name_and_description' => 'Tortang talong (eggplant omelette)', 'alternate_common_names' => 'eggplant egg, tortang', 'energy_kcal' => 120, 'nutrition_tags' => 'protein, vegetables, traditional dish'],
            ['food_name_and_description' => 'Baka (beef)', 'alternate_common_names' => 'beef, beef steak', 'energy_kcal' => 250, 'nutrition_tags' => 'protein, iron, zinc, vitamin B12'],
            ['food_name_and_description' => 'Bistek Tagalog (Filipino beef steak)', 'alternate_common_names' => 'beef steak Filipino style', 'energy_kcal' => 280, 'nutrition_tags' => 'protein, iron, traditional dish'],
            ['food_name_and_description' => 'Beef tapa (cured beef)', 'alternate_common_names' => 'tapang baka, cured beef', 'energy_kcal' => 250, 'nutrition_tags' => 'protein, traditional food, iron'],
            ['food_name_and_description' => 'Nilagang baka (beef soup)', 'alternate_common_names' => 'beef stew, boiled beef', 'energy_kcal' => 215, 'nutrition_tags' => 'protein, iron, traditional dish'],
            ['food_name_and_description' => 'Kare-kare (peanut stew)', 'alternate_common_names' => 'oxtail stew, peanut stew', 'energy_kcal' => 320, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Beef kaldereta (beef stew)', 'alternate_common_names' => 'beef caldereta', 'energy_kcal' => 290, 'nutrition_tags' => 'protein, iron, traditional dish'],
            ['food_name_and_description' => 'Beef mechado (beef in tomato sauce)', 'alternate_common_names' => 'mechado', 'energy_kcal' => 270, 'nutrition_tags' => 'protein, iron, traditional dish'],
            ['food_name_and_description' => 'Beef afritada (beef tomato stew)', 'alternate_common_names' => 'beef afritada', 'energy_kcal' => 265, 'nutrition_tags' => 'protein, iron, traditional dish'],
            
            // VEGETABLES - LEAFY GREENS
            ['food_name_and_description' => 'Kangkong (water spinach)', 'alternate_common_names' => 'water spinach, swamp cabbage', 'energy_kcal' => 19, 'nutrition_tags' => 'vegetable, vitamin A, vitamin C, iron, fiber'],
            ['food_name_and_description' => 'Ginisang kangkong (sauteed water spinach)', 'alternate_common_names' => 'adobong kangkong', 'energy_kcal' => 45, 'nutrition_tags' => 'vegetable, iron, vitamin A'],
            ['food_name_and_description' => 'Malunggay (moringa leaves)', 'alternate_common_names' => 'moringa, drumstick leaves', 'energy_kcal' => 64, 'nutrition_tags' => 'vegetable, vitamin A, calcium, iron, protein'],
            ['food_name_and_description' => 'Pechay (bok choy)', 'alternate_common_names' => 'Chinese cabbage, bok choy', 'energy_kcal' => 13, 'nutrition_tags' => 'vegetable, vitamin A, vitamin C, calcium'],
            ['food_name_and_description' => 'Repolyo (cabbage)', 'alternate_common_names' => 'cabbage', 'energy_kcal' => 25, 'nutrition_tags' => 'vegetable, vitamin C, fiber'],
            
            // VEGETABLES - FROM BAHAY KUBO & MORE
            ['food_name_and_description' => 'Kalabasa (squash)', 'alternate_common_names' => 'pumpkin, squash', 'energy_kcal' => 26, 'nutrition_tags' => 'vegetable, vitamin A, fiber'],
            ['food_name_and_description' => 'Ginataang kalabasa (squash in coconut milk)', 'alternate_common_names' => 'squash coconut', 'energy_kcal' => 90, 'nutrition_tags' => 'vegetable, vitamin A, traditional dish'],
            ['food_name_and_description' => 'Sitaw (string beans)', 'alternate_common_names' => 'long beans, yard-long beans', 'energy_kcal' => 47, 'nutrition_tags' => 'vegetable, fiber, vitamin C'],
            ['food_name_and_description' => 'Talong (eggplant)', 'alternate_common_names' => 'eggplant, aubergine', 'energy_kcal' => 25, 'nutrition_tags' => 'vegetable, fiber, antioxidants'],
            ['food_name_and_description' => 'Ampalaya (bitter gourd)', 'alternate_common_names' => 'bitter melon, bitter gourd', 'energy_kcal' => 17, 'nutrition_tags' => 'vegetable, vitamin C, fiber'],
            ['food_name_and_description' => 'Ginisang ampalaya (sauteed bitter gourd)', 'alternate_common_names' => 'ampalaya with egg', 'energy_kcal' => 60, 'nutrition_tags' => 'vegetable, vitamin C'],
            ['food_name_and_description' => 'Kamatis (tomato)', 'alternate_common_names' => 'tomato', 'energy_kcal' => 18, 'nutrition_tags' => 'vegetable, vitamin C, lycopene'],
            ['food_name_and_description' => 'Sibuyas (onion)', 'alternate_common_names' => 'onion', 'energy_kcal' => 40, 'nutrition_tags' => 'vegetable, flavor, antioxidants'],
            ['food_name_and_description' => 'Bawang (garlic)', 'alternate_common_names' => 'garlic', 'energy_kcal' => 149, 'nutrition_tags' => 'vegetable, flavor, antimicrobial'],
            ['food_name_and_description' => 'Patatas (potato)', 'alternate_common_names' => 'potato', 'energy_kcal' => 77, 'nutrition_tags' => 'carbohydrate, vitamin C, potassium'],
            ['food_name_and_description' => 'Kamote (sweet potato)', 'alternate_common_names' => 'sweet potato', 'energy_kcal' => 86, 'nutrition_tags' => 'carbohydrate, vitamin A, fiber'],
            
            // MORE BAHAY KUBO VEGETABLES
            ['food_name_and_description' => 'Singkamas (turnip/jicama)', 'alternate_common_names' => 'jicama, turnip, Mexican yam', 'energy_kcal' => 38, 'nutrition_tags' => 'vegetable, vitamin C, fiber'],
            ['food_name_and_description' => 'Labanos (radish)', 'alternate_common_names' => 'radish, white radish', 'energy_kcal' => 16, 'nutrition_tags' => 'vegetable, vitamin C, fiber'],
            ['food_name_and_description' => 'Mustasa (mustard greens)', 'alternate_common_names' => 'mustard greens, mustard leaves', 'energy_kcal' => 27, 'nutrition_tags' => 'vegetable, vitamin A, vitamin C, calcium'],
            ['food_name_and_description' => 'Sigarilyas (winged beans)', 'alternate_common_names' => 'winged beans, four-angled beans', 'energy_kcal' => 49, 'nutrition_tags' => 'vegetable, protein, fiber'],
            ['food_name_and_description' => 'Kondol (winter melon)', 'alternate_common_names' => 'winter melon, ash gourd', 'energy_kcal' => 13, 'nutrition_tags' => 'vegetable, vitamin C, low calorie'],
            ['food_name_and_description' => 'Patola (luffa/sponge gourd)', 'alternate_common_names' => 'luffa, sponge gourd, patola gourd', 'energy_kcal' => 20, 'nutrition_tags' => 'vegetable, vitamin C, fiber'],
            ['food_name_and_description' => 'Ginisang patola (sauteed luffa)', 'alternate_common_names' => 'sauteed patola with shrimp', 'energy_kcal' => 55, 'nutrition_tags' => 'vegetable, traditional dish'],
            ['food_name_and_description' => 'Upo (bottle gourd)', 'alternate_common_names' => 'bottle gourd, calabash', 'energy_kcal' => 14, 'nutrition_tags' => 'vegetable, vitamin C, low calorie'],
            ['food_name_and_description' => 'Ginisang upo (sauteed bottle gourd)', 'alternate_common_names' => 'sauteed upo with shrimp', 'energy_kcal' => 50, 'nutrition_tags' => 'vegetable, traditional dish'],
            ['food_name_and_description' => 'Sayote (chayote)', 'alternate_common_names' => 'chayote, mirliton', 'energy_kcal' => 19, 'nutrition_tags' => 'vegetable, vitamin C, fiber'],
            ['food_name_and_description' => 'Ginisang sayote (sauteed chayote)', 'alternate_common_names' => 'sauteed chayote', 'energy_kcal' => 45, 'nutrition_tags' => 'vegetable, traditional dish'],
            ['food_name_and_description' => 'Labong (bamboo shoots)', 'alternate_common_names' => 'bamboo shoots', 'energy_kcal' => 27, 'nutrition_tags' => 'vegetable, fiber, potassium'],
            ['food_name_and_description' => 'Ginataang labong (bamboo shoots in coconut)', 'alternate_common_names' => 'bamboo shoots coconut milk', 'energy_kcal' => 85, 'nutrition_tags' => 'vegetable, traditional dish'],
            ['food_name_and_description' => 'Talbos ng kamote (sweet potato leaves)', 'alternate_common_names' => 'sweet potato tops, camote tops', 'energy_kcal' => 35, 'nutrition_tags' => 'vegetable, vitamin A, iron, calcium'],
            ['food_name_and_description' => 'Ginisang talbos ng kamote (sauteed sweet potato leaves)', 'alternate_common_names' => 'adobong talbos ng kamote', 'energy_kcal' => 55, 'nutrition_tags' => 'vegetable, iron, vitamin A'],
            ['food_name_and_description' => 'Alugbati (Malabar spinach)', 'alternate_common_names' => 'Malabar spinach, Ceylon spinach', 'energy_kcal' => 19, 'nutrition_tags' => 'vegetable, vitamin A, iron, calcium'],
            ['food_name_and_description' => 'Saluyot (jute leaves)', 'alternate_common_names' => 'jute leaves, jute mallow', 'energy_kcal' => 37, 'nutrition_tags' => 'vegetable, vitamin A, vitamin C, calcium'],
            ['food_name_and_description' => 'Okra', 'alternate_common_names' => 'ladies fingers, okra', 'energy_kcal' => 33, 'nutrition_tags' => 'vegetable, fiber, vitamin C'],
            ['food_name_and_description' => 'Ginisang okra (sauteed okra)', 'alternate_common_names' => 'sauteed okra with tomato', 'energy_kcal' => 50, 'nutrition_tags' => 'vegetable, fiber'],
            ['food_name_and_description' => 'Carrots (karot)', 'alternate_common_names' => 'carrot, karot', 'energy_kcal' => 41, 'nutrition_tags' => 'vegetable, vitamin A, beta-carotene'],
            ['food_name_and_description' => 'Lettuce (litsugas)', 'alternate_common_names' => 'lettuce, litsugas, salad greens', 'energy_kcal' => 15, 'nutrition_tags' => 'vegetable, vitamin A, fiber'],
            ['food_name_and_description' => 'Cucumber (pipino)', 'alternate_common_names' => 'cucumber, pipino', 'energy_kcal' => 15, 'nutrition_tags' => 'vegetable, hydrating, vitamin K'],
            ['food_name_and_description' => 'Broccoli', 'alternate_common_names' => 'broccoli', 'energy_kcal' => 34, 'nutrition_tags' => 'vegetable, vitamin C, fiber, calcium'],
            ['food_name_and_description' => 'Cauliflower', 'alternate_common_names' => 'cauliflower', 'energy_kcal' => 25, 'nutrition_tags' => 'vegetable, vitamin C, fiber'],
            ['food_name_and_description' => 'Habitchuelas (green beans)', 'alternate_common_names' => 'green beans, baguio beans', 'energy_kcal' => 31, 'nutrition_tags' => 'vegetable, fiber, vitamin C'],
            
            // VEGETABLES - TRADITIONAL DISHES
            ['food_name_and_description' => 'Pinakbet (vegetable stew)', 'alternate_common_names' => 'mixed vegetables with bagoong', 'energy_kcal' => 95, 'nutrition_tags' => 'vegetable, traditional dish, fiber'],
            ['food_name_and_description' => 'Ginisang munggo (sauteed mung beans)', 'alternate_common_names' => 'mung bean stew, monggo', 'energy_kcal' => 120, 'nutrition_tags' => 'protein, vegetable, fiber, traditional dish'],
            
            // FRUITS - EXPANDED
            ['food_name_and_description' => 'Saging (banana)', 'alternate_common_names' => 'banana', 'energy_kcal' => 89, 'nutrition_tags' => 'fruit, potassium, vitamin B6, energy'],
            ['food_name_and_description' => 'Hinog na saging (ripe banana)', 'alternate_common_names' => 'ripe banana, mature banana', 'energy_kcal' => 89, 'nutrition_tags' => 'fruit, easy to digest, for babies'],
            ['food_name_and_description' => 'Nilagang saging (boiled banana)', 'alternate_common_names' => 'boiled saba, cooked banana', 'energy_kcal' => 122, 'nutrition_tags' => 'fruit, carbohydrate'],
            ['food_name_and_description' => 'Saging na saba (plantain banana)', 'alternate_common_names' => 'cooking banana, saba', 'energy_kcal' => 122, 'nutrition_tags' => 'fruit, carbohydrate, potassium'],
            ['food_name_and_description' => 'Mangga (mango)', 'alternate_common_names' => 'mango', 'energy_kcal' => 60, 'nutrition_tags' => 'fruit, vitamin A, vitamin C'],
            ['food_name_and_description' => 'Hilaw na mangga (green mango)', 'alternate_common_names' => 'green mango, unripe mango', 'energy_kcal' => 40, 'nutrition_tags' => 'fruit, vitamin C, fiber'],
            ['food_name_and_description' => 'Papaya', 'alternate_common_names' => 'papaya fruit', 'energy_kcal' => 43, 'nutrition_tags' => 'fruit, vitamin C, vitamin A, digestive enzymes'],
            ['food_name_and_description' => 'Pakwan (watermelon)', 'alternate_common_names' => 'watermelon', 'energy_kcal' => 30, 'nutrition_tags' => 'fruit, hydrating, vitamin C'],
            ['food_name_and_description' => 'Suha (pomelo)', 'alternate_common_names' => 'pomelo, grapefruit', 'energy_kcal' => 38, 'nutrition_tags' => 'fruit, vitamin C, fiber'],
            ['food_name_and_description' => 'Dalandan (orange)', 'alternate_common_names' => 'Philippine orange, orange', 'energy_kcal' => 47, 'nutrition_tags' => 'fruit, vitamin C'],
            ['food_name_and_description' => 'Kalamansi (calamansi)', 'alternate_common_names' => 'Philippine lime, calamansi', 'energy_kcal' => 29, 'nutrition_tags' => 'fruit, vitamin C, citrus'],
            ['food_name_and_description' => 'Bayabas (guava)', 'alternate_common_names' => 'guava', 'energy_kcal' => 68, 'nutrition_tags' => 'fruit, vitamin C, fiber'],
            ['food_name_and_description' => 'Pinya (pineapple)', 'alternate_common_names' => 'pineapple', 'energy_kcal' => 50, 'nutrition_tags' => 'fruit, vitamin C, bromelain'],
            ['food_name_and_description' => 'Santol', 'alternate_common_names' => 'cotton fruit, santol', 'energy_kcal' => 75, 'nutrition_tags' => 'fruit, vitamin C, fiber'],
            ['food_name_and_description' => 'Lanzones', 'alternate_common_names' => 'langsat, lanzones', 'energy_kcal' => 57, 'nutrition_tags' => 'fruit, vitamin C, potassium'],
            ['food_name_and_description' => 'Rambutan', 'alternate_common_names' => 'rambutan', 'energy_kcal' => 68, 'nutrition_tags' => 'fruit, vitamin C, iron'],
            ['food_name_and_description' => 'Duhat (black plum)', 'alternate_common_names' => 'java plum, duhat', 'energy_kcal' => 60, 'nutrition_tags' => 'fruit, vitamin C, antioxidants'],
            ['food_name_and_description' => 'Siniguelas (Spanish plum)', 'alternate_common_names' => 'Spanish plum, siniguelas', 'energy_kcal' => 48, 'nutrition_tags' => 'fruit, vitamin C'],
            ['food_name_and_description' => 'Atis (sugar apple)', 'alternate_common_names' => 'custard apple, sugar apple', 'energy_kcal' => 94, 'nutrition_tags' => 'fruit, vitamin C, energy'],
            ['food_name_and_description' => 'Guyabano (soursop)', 'alternate_common_names' => 'soursop, guyabano', 'energy_kcal' => 66, 'nutrition_tags' => 'fruit, vitamin C, fiber'],
            ['food_name_and_description' => 'Melon', 'alternate_common_names' => 'cantaloupe, melon', 'energy_kcal' => 34, 'nutrition_tags' => 'fruit, vitamin A, vitamin C'],
            ['food_name_and_description' => 'Grapes (ubas)', 'alternate_common_names' => 'grapes, ubas', 'energy_kcal' => 69, 'nutrition_tags' => 'fruit, antioxidants, energy'],
            ['food_name_and_description' => 'Apple (mansanas)', 'alternate_common_names' => 'apple, mansanas', 'energy_kcal' => 52, 'nutrition_tags' => 'fruit, fiber, vitamin C'],
            ['food_name_and_description' => 'Pear (peras)', 'alternate_common_names' => 'pear, peras', 'energy_kcal' => 57, 'nutrition_tags' => 'fruit, fiber, vitamin C'],
            ['food_name_and_description' => 'Avocado', 'alternate_common_names' => 'avocado, alligator pear', 'energy_kcal' => 160, 'nutrition_tags' => 'fruit, healthy fats, potassium, fiber'],
            
            // SNACKS & TRADITIONAL FILIPINO FOODS - EXPANDED
            ['food_name_and_description' => 'Turon (banana spring rolls)', 'alternate_common_names' => 'banana lumpia, fried banana roll', 'energy_kcal' => 180, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Banana cue (caramelized banana)', 'alternate_common_names' => 'caramelized saba, banana-q', 'energy_kcal' => 150, 'nutrition_tags' => 'snack, traditional food'],
            ['food_name_and_description' => 'Kamote cue (caramelized sweet potato)', 'alternate_common_names' => 'caramelized kamote, sweet potato cue', 'energy_kcal' => 160, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Champorado (chocolate rice porridge)', 'alternate_common_names' => 'chocolate rice, tsampurado', 'energy_kcal' => 180, 'nutrition_tags' => 'breakfast, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Ginataang bilo-bilo (sweet rice balls)', 'alternate_common_names' => 'bilo-bilo, rice balls in coconut milk', 'energy_kcal' => 200, 'nutrition_tags' => 'snack, traditional food, dessert'],
            ['food_name_and_description' => 'Ginataang mais (corn in coconut milk)', 'alternate_common_names' => 'sweet corn coconut', 'energy_kcal' => 160, 'nutrition_tags' => 'snack, traditional food'],
            ['food_name_and_description' => 'Ginataang halo-halo (mixed fruits in coconut)', 'alternate_common_names' => 'mixed fruits coconut milk', 'energy_kcal' => 180, 'nutrition_tags' => 'snack, traditional food, dessert'],
            ['food_name_and_description' => 'Puto (steamed rice cake)', 'alternate_common_names' => 'rice cake, steamed cake', 'energy_kcal' => 120, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Puto bumbong (purple rice cake)', 'alternate_common_names' => 'purple sticky rice cake', 'energy_kcal' => 145, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Kutsinta (brown rice cake)', 'alternate_common_names' => 'brown sticky cake', 'energy_kcal' => 130, 'nutrition_tags' => 'snack, traditional food'],
            ['food_name_and_description' => 'Sapin-sapin (layered rice cake)', 'alternate_common_names' => 'layered sticky cake', 'energy_kcal' => 155, 'nutrition_tags' => 'snack, traditional food, dessert'],
            ['food_name_and_description' => 'Suman (sticky rice)', 'alternate_common_names' => 'rice wrapped in banana leaves', 'energy_kcal' => 150, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Bibingka (rice cake)', 'alternate_common_names' => 'baked rice cake', 'energy_kcal' => 180, 'nutrition_tags' => 'snack, traditional food'],
            ['food_name_and_description' => 'Palitaw (sweet rice cakes)', 'alternate_common_names' => 'floating rice cake', 'energy_kcal' => 140, 'nutrition_tags' => 'snack, traditional food'],
            ['food_name_and_description' => 'Pichi-pichi (cassava cake)', 'alternate_common_names' => 'cassava jelly cake', 'energy_kcal' => 135, 'nutrition_tags' => 'snack, traditional food, dessert'],
            ['food_name_and_description' => 'Maja blanca (coconut pudding)', 'alternate_common_names' => 'coconut pudding, maja', 'energy_kcal' => 170, 'nutrition_tags' => 'snack, traditional food, dessert'],
            ['food_name_and_description' => 'Leche flan (caramel custard)', 'alternate_common_names' => 'Filipino caramel custard', 'energy_kcal' => 300, 'nutrition_tags' => 'dessert, traditional food, dairy'],
            ['food_name_and_description' => 'Halo-halo (mixed dessert)', 'alternate_common_names' => 'Filipino mixed dessert', 'energy_kcal' => 250, 'nutrition_tags' => 'dessert, traditional food, dairy'],
            ['food_name_and_description' => 'Buko salad (young coconut salad)', 'alternate_common_names' => 'coconut fruit salad', 'energy_kcal' => 180, 'nutrition_tags' => 'dessert, traditional food'],
            ['food_name_and_description' => 'Ensaymada (sweet bread)', 'alternate_common_names' => 'Filipino brioche', 'energy_kcal' => 280, 'nutrition_tags' => 'snack, bread, traditional food'],
            ['food_name_and_description' => 'Pan de coco (coconut bread)', 'alternate_common_names' => 'coconut filled bread', 'energy_kcal' => 250, 'nutrition_tags' => 'snack, bread, traditional food'],
            ['food_name_and_description' => 'Ube halaya (purple yam jam)', 'alternate_common_names' => 'purple yam, ube jam', 'energy_kcal' => 150, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Polvoron (milk candy)', 'alternate_common_names' => 'Filipino shortbread', 'energy_kcal' => 140, 'nutrition_tags' => 'snack, traditional food'],
            ['food_name_and_description' => 'Yema (custard candy)', 'alternate_common_names' => 'Filipino milk candy', 'energy_kcal' => 160, 'nutrition_tags' => 'snack, traditional food, dairy'],
            ['food_name_and_description' => 'Pastillas (milk candy)', 'alternate_common_names' => 'milk fudge', 'energy_kcal' => 155, 'nutrition_tags' => 'snack, traditional food, dairy'],
            ['food_name_and_description' => 'Cassava cake (kakanin)', 'alternate_common_names' => 'cassava pudding', 'energy_kcal' => 190, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            ['food_name_and_description' => 'Biko (sweet rice cake)', 'alternate_common_names' => 'sticky sweet rice', 'energy_kcal' => 210, 'nutrition_tags' => 'snack, traditional food, carbohydrate'],
            
            // DAIRY & OTHERS - EXPANDED
            ['food_name_and_description' => 'Gatas (milk)', 'alternate_common_names' => 'fresh milk, cow milk', 'energy_kcal' => 61, 'nutrition_tags' => 'dairy, calcium, protein, vitamin D'],
            ['food_name_and_description' => 'Gatas ng kalabaw (carabao milk)', 'alternate_common_names' => 'carabao milk, water buffalo milk', 'energy_kcal' => 97, 'nutrition_tags' => 'dairy, calcium, protein, rich'],
            ['food_name_and_description' => 'Kesong puti (white cheese)', 'alternate_common_names' => 'Filipino white cheese, fresh cheese', 'energy_kcal' => 264, 'nutrition_tags' => 'dairy, protein, calcium'],
            ['food_name_and_description' => 'Yogurt', 'alternate_common_names' => 'yoghurt', 'energy_kcal' => 59, 'nutrition_tags' => 'dairy, protein, probiotics, calcium'],
            ['food_name_and_description' => 'Greek yogurt', 'alternate_common_names' => 'Greek-style yogurt', 'energy_kcal' => 97, 'nutrition_tags' => 'dairy, protein, probiotics, calcium'],
            ['food_name_and_description' => 'Cheese (keso)', 'alternate_common_names' => 'cheese, keso, Eden cheese', 'energy_kcal' => 402, 'nutrition_tags' => 'dairy, protein, calcium'],
            ['food_name_and_description' => 'Butter (mantekilya)', 'alternate_common_names' => 'butter, mantekilya', 'energy_kcal' => 717, 'nutrition_tags' => 'dairy, fats'],
            
            // SOUPS & STEWS - EXPANDED
            ['food_name_and_description' => 'Sopas (Filipino chicken macaroni soup)', 'alternate_common_names' => 'chicken soup, macaroni soup', 'energy_kcal' => 130, 'nutrition_tags' => 'soup, traditional dish, comfort food'],
            ['food_name_and_description' => 'Afritada (chicken/pork in tomato sauce)', 'alternate_common_names' => 'Filipino stew', 'energy_kcal' => 220, 'nutrition_tags' => 'traditional dish, protein'],
            ['food_name_and_description' => 'Mechado (beef stew)', 'alternate_common_names' => 'beef in tomato sauce', 'energy_kcal' => 260, 'nutrition_tags' => 'traditional dish, protein'],
            ['food_name_and_description' => 'Caldereta (goat/beef stew)', 'alternate_common_names' => 'Filipino stew', 'energy_kcal' => 280, 'nutrition_tags' => 'traditional dish, protein'],
            ['food_name_and_description' => 'Bulalo (beef bone marrow soup)', 'alternate_common_names' => 'beef shank soup, bone marrow soup', 'energy_kcal' => 240, 'nutrition_tags' => 'soup, traditional dish, protein'],
            ['food_name_and_description' => 'Pochero (Filipino beef stew)', 'alternate_common_names' => 'beef vegetable stew', 'energy_kcal' => 210, 'nutrition_tags' => 'soup, traditional dish, protein'],
            ['food_name_and_description' => 'Nilaga na pata (pork leg soup)', 'alternate_common_names' => 'pork hock soup', 'energy_kcal' => 230, 'nutrition_tags' => 'soup, traditional dish, protein'],
            ['food_name_and_description' => 'Lauya (chicken ginger soup Ilocano)', 'alternate_common_names' => 'Ilocano chicken soup', 'energy_kcal' => 125, 'nutrition_tags' => 'soup, traditional dish, protein'],
            ['food_name_and_description' => 'Binacol (chicken coconut soup)', 'alternate_common_names' => 'chicken cooked in bamboo', 'energy_kcal' => 155, 'nutrition_tags' => 'soup, traditional dish, protein'],
            ['food_name_and_description' => 'Laswa (vegetable soup)', 'alternate_common_names' => 'Ilonggo vegetable soup', 'energy_kcal' => 60, 'nutrition_tags' => 'soup, vegetable, traditional dish'],
            ['food_name_and_description' => 'Monggo soup (mung bean soup)', 'alternate_common_names' => 'mung bean soup', 'energy_kcal' => 95, 'nutrition_tags' => 'soup, protein, fiber'],
            
            // MODERN FILIPINO FUSION & POPULAR DISHES
            ['food_name_and_description' => 'Spaghetti (Filipino-style)', 'alternate_common_names' => 'sweet spaghetti, Filipino spaghetti', 'energy_kcal' => 220, 'nutrition_tags' => 'pasta, modern Filipino'],
            ['food_name_and_description' => 'Carbonara (Filipino-style)', 'alternate_common_names' => 'creamy pasta, carbonara', 'energy_kcal' => 350, 'nutrition_tags' => 'pasta, modern Filipino, dairy'],
            ['food_name_and_description' => 'Fried chicken (pritong manok)', 'alternate_common_names' => 'fried chicken, crispy chicken', 'energy_kcal' => 280, 'nutrition_tags' => 'protein, modern Filipino'],
            ['food_name_and_description' => 'Longganisa (Filipino sausage)', 'alternate_common_names' => 'Filipino pork sausage', 'energy_kcal' => 290, 'nutrition_tags' => 'protein, traditional food'],
            ['food_name_and_description' => 'Tocino (sweet cured pork)', 'alternate_common_names' => 'sweet pork, cured pork', 'energy_kcal' => 310, 'nutrition_tags' => 'protein, traditional food'],
            ['food_name_and_description' => 'Tapa (cured beef)', 'alternate_common_names' => 'beef tapa, cured beef', 'energy_kcal' => 250, 'nutrition_tags' => 'protein, traditional food'],
            ['food_name_and_description' => 'Lumpia Shanghai (spring rolls)', 'alternate_common_names' => 'Filipino spring rolls, egg rolls', 'energy_kcal' => 200, 'nutrition_tags' => 'snack, traditional food, protein'],
            ['food_name_and_description' => 'Lumpiang sariwa (fresh spring rolls)', 'alternate_common_names' => 'fresh vegetable rolls', 'energy_kcal' => 120, 'nutrition_tags' => 'snack, traditional food, vegetable'],
            ['food_name_and_description' => 'Empanada (meat pie)', 'alternate_common_names' => 'Filipino meat pie, turnover', 'energy_kcal' => 240, 'nutrition_tags' => 'snack, traditional food'],
            ['food_name_and_description' => 'Palabok (noodles with sauce)', 'alternate_common_names' => 'rice noodles with shrimp sauce', 'energy_kcal' => 280, 'nutrition_tags' => 'noodles, traditional dish'],
            ['food_name_and_description' => 'Mami (Filipino noodle soup)', 'alternate_common_names' => 'Filipino ramen, noodle soup', 'energy_kcal' => 210, 'nutrition_tags' => 'soup, noodles, comfort food'],
            ['food_name_and_description' => 'Goto (rice porridge with tripe)', 'alternate_common_names' => 'tripe porridge, goto', 'energy_kcal' => 160, 'nutrition_tags' => 'soup, traditional dish, protein'],
            ['food_name_and_description' => 'Dinuguan (pork blood stew)', 'alternate_common_names' => 'chocolate meat, pork blood stew', 'energy_kcal' => 180, 'nutrition_tags' => 'traditional dish, protein, iron'],
            ['food_name_and_description' => 'Sisig (sizzling pork)', 'alternate_common_names' => 'sizzling pork sisig', 'energy_kcal' => 320, 'nutrition_tags' => 'traditional dish, protein'],
            ['food_name_and_description' => 'Lechon kawali (crispy pork belly)', 'alternate_common_names' => 'crispy fried pork', 'energy_kcal' => 420, 'nutrition_tags' => 'protein, traditional dish'],
            ['food_name_and_description' => 'Bicol express (spicy pork)', 'alternate_common_names' => 'spicy pork in coconut milk', 'energy_kcal' => 310, 'nutrition_tags' => 'protein, traditional dish, spicy'],
            ['food_name_and_description' => 'Laing (taro leaves in coconut)', 'alternate_common_names' => 'taro leaves, gabi leaves', 'energy_kcal' => 120, 'nutrition_tags' => 'vegetable, traditional dish'],
            ['food_name_and_description' => 'Pancit Malabon (thick noodles)', 'alternate_common_names' => 'seafood noodles', 'energy_kcal' => 250, 'nutrition_tags' => 'noodles, traditional dish, seafood'],
            ['food_name_and_description' => 'Arroz valenciana (Filipino paella)', 'alternate_common_names' => 'Filipino rice paella', 'energy_kcal' => 290, 'nutrition_tags' => 'rice, traditional dish, protein'],
            ['food_name_and_description' => 'Kilawin/Kinilaw (Filipino ceviche)', 'alternate_common_names' => 'raw fish salad, ceviche', 'energy_kcal' => 110, 'nutrition_tags' => 'seafood, traditional dish, protein'],
        ];

        // Check for duplicates and insert only new foods
        $inserted = 0;
        $skipped = 0;
        
        foreach ($foods as $food) {
            // Check if food already exists
            $exists = DB::table('foods')
                ->where('food_name_and_description', $food['food_name_and_description'])
                ->exists();
            
            if (!$exists) {
                DB::table('foods')->insert([
                    'food_name_and_description' => $food['food_name_and_description'],
                    'alternate_common_names' => $food['alternate_common_names'],
                    'energy_kcal' => $food['energy_kcal'],
                    'nutrition_tags' => $food['nutrition_tags'],
                ]);
                $inserted++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("✅ Successfully seeded {$inserted} new Filipino foods!");
        if ($skipped > 0) {
            $this->command->info("ℹ️  Skipped {$skipped} existing foods (no duplicates)");
        }
        $this->command->info("📊 Total foods in seeder: " . count($foods));
    }
}
