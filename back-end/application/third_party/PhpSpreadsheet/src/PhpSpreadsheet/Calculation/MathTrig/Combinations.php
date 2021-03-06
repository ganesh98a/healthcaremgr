<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\MathTrig;

use Exception;

class Combinations
{
    /**
     * COMBIN.
     *
     * Returns the number of combinations for a given number of items. Use COMBIN to
     *        determine the total possible number of groups for a given number of items.
     *
     * Excel Function:
     *        COMBIN(numObjs,numInSet)
     *
     * @param mixed $numObjs Number of different objects
     * @param mixed $numInSet Number of objects in each combination
     *
     * @return float|int|string Number of combinations, or a string containing an error
     */
    public static function withoutRepetition($numObjs, $numInSet)
    {
        try {
            $numObjs = Helpers::validateNumericNullSubstitution($numObjs, null);
            $numInSet = Helpers::validateNumericNullSubstitution($numInSet, null);
            Helpers::validateNotNegative($numInSet);
            Helpers::validateNotNegative($numObjs - $numInSet);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return round(Fact::evaluate($numObjs) / Fact::evaluate($numObjs - $numInSet)) / Fact::evaluate($numInSet);
    }

    /**
     * COMBIN.
     *
     * Returns the number of combinations for a given number of items. Use COMBIN to
     *        determine the total possible number of groups for a given number of items.
     *
     * Excel Function:
     *        COMBIN(numObjs,numInSet)
     *
     * @param mixed $numObjs Number of different objects
     * @param mixed $numInSet Number of objects in each combination
     *
     * @return float|int|string Number of combinations, or a string containing an error
     */
    public static function withRepetition($numObjs, $numInSet)
    {
        try {
            $numObjs = Helpers::validateNumericNullSubstitution($numObjs, null);
            $numInSet = Helpers::validateNumericNullSubstitution($numInSet, null);
            Helpers::validateNotNegative($numInSet);
            Helpers::validateNotNegative($numObjs);
            $numObjs = (int) $numObjs;
            $numInSet = (int) $numInSet;
            // Microsoft documentation says following is true, but Excel
            //  does not enforce this restriction.
            //Helpers::validateNotNegative($numObjs - $numInSet);
            if ($numObjs === 0) {
                Helpers::validateNotNegative(-$numInSet);

                return 1;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return round(Fact::evaluate($numObjs + $numInSet - 1) / Fact::evaluate($numObjs - 1)) / Fact::evaluate($numInSet);
    }
}
