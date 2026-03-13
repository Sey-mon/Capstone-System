
import unittest

from feeding_program_chain import validate_meal_plan


class FeedingProgramQualityTests(unittest.TestCase):
    def _base_plan(self):
        return {
            "meal_plan": [
                {
                    "day": 1,
                    "meals": [
                        {
                            "meal_name_tagalog": "Almusal",
                            "dish_name": "Arroz Caldo",
                            "ingredients": ["2 cups rice", "1 kg manok", "garlic", "ginger"],
                            "portions": "1 cup",
                        },
                        {
                            "meal_name_tagalog": "Tanghalian",
                            "dish_name": "Tinolang Manok",
                            "ingredients": ["1 kg manok", "malunggay", "rice"],
                            "portions": "1 cup",
                        },
                        {
                            "meal_name_tagalog": "Meryenda",
                            "dish_name": "Saging na Nilaga",
                            "ingredients": ["saging"],
                            "portions": "1 piece",
                        },
                        {
                            "meal_name_tagalog": "Hapunan",
                            "dish_name": "Adobong Manok",
                            "ingredients": ["manok", "toyo", "suka", "rice"],
                            "portions": "1 cup",
                        },
                    ],
                }
            ]
        }

    def test_validate_meal_plan_passes_for_valid_structure(self):
        plan = self._base_plan()
        issues, dishes = validate_meal_plan(plan, expected_days=1)
        self.assertEqual([], issues)
        self.assertEqual(4, len(dishes))

    def test_validate_meal_plan_flags_duplicate_dish(self):
        plan = self._base_plan()
        plan["meal_plan"][0]["meals"][1]["dish_name"] = "Arroz Caldo"
        issues, _ = validate_meal_plan(plan, expected_days=1)
        self.assertTrue(any("Duplicate dish" in i for i in issues))

    def test_strict_mode_blocks_outside_ingredient(self):
        plan = self._base_plan()
        # strict list allows only chicken + rice (plus condiments)
        # "malunggay" should be blocked by the hard post-generation gate.
        issues, _ = validate_meal_plan(
            plan,
            expected_days=1,
            available_ingredients="manok, rice",
        )
        self.assertTrue(any("outside allowed list" in i.lower() for i in issues))

    def test_strict_mode_allows_condiments(self):
        plan = self._base_plan()
        for meal in plan["meal_plan"][0]["meals"]:
            meal["ingredients"] = ["manok", "rice", "garlic", "onion", "salt", "toyo", "luya"]
        issues, _ = validate_meal_plan(
            plan,
            expected_days=1,
            available_ingredients="manok, rice",
        )
        self.assertFalse(any("outside allowed list" in i.lower() for i in issues))

    def test_day_count_mismatch_is_reported(self):
        plan = self._base_plan()
        issues, _ = validate_meal_plan(plan, expected_days=2)
        self.assertTrue(any("Day count mismatch" in i for i in issues))

    def test_missing_meal_plan_key_is_rejected(self):
        issues, _ = validate_meal_plan({"not_meal_plan": []}, expected_days=1)
        self.assertTrue(any("Missing 'meal_plan'" in i for i in issues))

    def test_wrong_meal_order_is_reported(self):
        plan = self._base_plan()
        plan["meal_plan"][0]["meals"][0]["meal_name_tagalog"] = "Tanghalian"
        issues, _ = validate_meal_plan(plan, expected_days=1)
        self.assertTrue(any("Expected 'Almusal'" in i for i in issues))

    def test_prohibited_dish_name_is_reported(self):
        plan = self._base_plan()
        plan["meal_plan"][0]["meals"][3]["dish_name"] = "Hotdog with Rice"
        issues, _ = validate_meal_plan(plan, expected_days=1)
        self.assertTrue(any("Prohibited ingredient in dish name" in i for i in issues))

    def test_non_list_ingredients_in_strict_mode_is_rejected(self):
        plan = self._base_plan()
        plan["meal_plan"][0]["meals"][1]["ingredients"] = "manok, rice"
        issues, _ = validate_meal_plan(
            plan,
            expected_days=1,
            available_ingredients="manok, rice",
        )
        self.assertTrue(any("'ingredients' must be a list" in i for i in issues))


if __name__ == "__main__":
    unittest.main()
