<?php

/**
 * @copyright Copyright (C) 2015-2022 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\actions\api\internal;

use Yii;
use app\actions\api\internal\latestBattles\UserFormatter;
use app\components\helpers\CombinedBattles;
use app\models\User;
use yii\db\Transaction;
use yii\helpers\Url;

final class MyLatestBattlesAction extends BaseLatestBattlesAction
{
    use UserFormatter;

    private const BATTLE_LIMIT = 12;

    protected function isPrecheckOK(): bool
    {
        return !Yii::$app->user->isGuest;
    }

    protected function fetchBattles(): array
    {
        return Yii::$app->db->transaction(
            function (): array {
                return CombinedBattles::getUserRecentBattles(
                    Yii::$app->user->identity,
                    static::BATTLE_LIMIT
                );
            },
            Transaction::REPEATABLE_READ
        );
    }

    public function run()
    {
        $json = parent::run();
        if ($json['battles']) {
            $json['user'] = self::formatUser(Yii::$app->user->identity);
        }
        return $json;
    }

    protected function getHeading(): string
    {
        return Yii::t('app', '{name}\'s Battles');
    }
}
