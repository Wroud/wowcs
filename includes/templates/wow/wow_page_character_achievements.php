<div xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" style="display: none;">
<?php
$categoryID = WoW_Achievements::GetCategoryForTemplate();
if(!$categoryID) {
    WoW_Log::WriteError('Achievements : categoryID is not defined!');
    exit;
}
$categoryInfo = WoW_Achievements::GetCategoryInfoFromDB($categoryID);
if(!$categoryInfo) {
    WoW_Log::WriteError('Achievements : categoryInfo for categoryID %d was not found!', $categoryID);
    exit;
}
$achievements = WoW_Achievements::AchievementCategory($categoryID);
if(!$achievements) {
    WoW_Log::WriteError('Achievements : achievements for categoryID %d was not found!', $categoryID);
    exit;
}
?>
<div id="cat-<?php echo $categoryInfo['id']; ?>" class="container<?php if($categoryID == 81) echo ' feats'; ?>">
<h3 class="category"><?php echo $categoryInfo['name']; ?></h3><?php
    if($categoryID != 81) {
        echo '
    <div class="profile-progress border-4" onmouseover="Tooltip.show(this, &#39;2 675 / 5 530 очков&#39;, { location: &#39;middleRight&#39; });">
        <div class="bar border-4" style="width: 50%"></div>
        <div class="bar-contents">259 / 513 (50%)</div>
    </div>';
    }
    ?>
    
    <ul><?php
    foreach($achievements as $ach) {
        //print_r($ach);
        $criterias_list = null;
        $criterias_failed = false;
        if(is_array($ach['criterias'])) {
            $list_opened = false;
            $criterias_list_header = '<div class="icon-expandable"></div><div class="meta-achievements">%s</div>';
            foreach($ach['criterias'] as $criteria) {
                if(!is_array($criteria) || !isset($criteria['name'])) {
                    $criterias_failed = true;
                    break;
                }
                if($criteria['progressBar']) {
                    if(!isset($criteria['maxQuantityGold'])) {
                        // Counter
                        $progressBar = sprintf('<div class="bar-contents">%d/ %d (%d%%)</div>',
                            $criteria['quantity'], $criteria['maxQuantity'], round(WoW_Utils::GetPercent($criteria['maxQuantity'], $criteria['quantity']))
                        );
                    }
                    else {
                        // Money
                        $progressBar = sprintf('<div class="bar-contents">
                        <span class="icon-gold">%d</span>
                        <span class="icon-silver">%d</span>
                        <span class="icon-copper">%d</span>
                        </div>', $criteria['quantityGold'], $criteria['quantitySilver'], $criteria['quantityCopper']);
                    }
                    $criterias_list .= sprintf('<div class="profile-progress border-4%s">
                    <div class="bar border-4" style="width: %d%%"></div>
                    %s
                    </div>', $criteria['quantity'] >= $criteria['maxQuantity'] ? ' completed' : null, WoW_Utils::GetPercent($criteria['maxQuantity'], $criteria['quantity']), $progressBar);
                }
                else {
                    if(!$list_opened) {
                        $criterias_list .= '<ul>';
                        $list_opened = true;
                    }
                    if(isset($criteria['achievementCriteria'])) {
                        // ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT
                        $criterias_list .= sprintf('<li class="%s linked">
                        <a href="#%d:a%d" onclick="location.hash = \'%d:a%d\'; dm.openEntry(true)">
                        <span  class="icon-frame frame-18" style=\'background-image: url("http://eu.battle.net/wow-assets/static/images/icons/18/%s.jpg");\'></span>
                        %s</a>
                        </li>', $criteria['achievementCriteria']['completed'] ? 'unlocked' : null,
                        $criteria['achievementCriteria']['categoryId'], $criteria['achievementCriteria']['id'],
                        $criteria['achievementCriteria']['categoryId'], $criteria['achievementCriteria']['id'],
                        $criteria['achievementCriteria']['iconname'], $criteria['achievementCriteria']['name']
                        );
                    }
                    else {
                        $criterias_list .= sprintf('<li%s>%s</li>', (isset($criteria['counter']) && ($criteria['counter'] > 0 && $criteria['date'] > 0)) ? ' class="unlocked"' : null, $criteria['name']);
                    }
                }
            }
            if($list_opened) {
                $criterias_list .= '</ul>';
            }
        }
        if($criterias_list != null) {
            $criterias_list = sprintf($criterias_list_header, $criterias_list);
        }
        if(isset($ach['reward_item'])) {
            $tmp_str = explode(':', $ach['titleReward']);
            $ach['titleReward'] = sprintf('%s: <a href="/wow/item/%d" class="color-q%d">%s</a>', $tmp_str[0], $ach['reward_item']['entry'], $ach['reward_item']['Quality'], $ach['reward_item']['name']);
        }
        echo sprintf('
        <li class="achievement %s" data-id="%d" data-href="#%d:a%d">
            <p>
                <strong>%s</strong><span>%s</span>
            </p>
            <a href="javascript:;" data-fansite="achievement|%d" class="fansite-link "> </a>
            %s
            <span class="icon-frame frame-50">
                <img src="http://eu.battle.net/wow-assets/static/images/icons/56/%s.jpg" alt="" width="50" height="50" />
            </span>
            <div class="points-big border-8"><strong>%d</strong>%s</div>%s
        </li>',
            !isset($ach['dateCompleted']) ? 'locked' : null,
            $ach['id'], $categoryID, $ach['id'], $ach['name'], $ach['desc'],
            $ach['id'], $criterias_list, $ach['iconname'],
            $ach['points'],
            isset($ach['dateCompleted']) ? sprintf('<span class="date">%s</span>', date('d/m/Y', $ach['dateCompleted'])) : null,
            $ach['titleReward'] != null ? sprintf('<div class="reward">%s</div>', $ach['titleReward']) : null
        );
    }
    ?>
    
    </ul>
    </div>
</div>