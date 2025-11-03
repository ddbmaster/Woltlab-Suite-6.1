{include file='header'}

<section class="section">
    <header class="sectionHeader">
        <h1>{lang}wcf.cs2bannliste.edit{/lang}</h1>
    </header>

    {if $error}
        <p style="color:#c00; font-weight:bold;">{$error}</p>
    {elseif $success}
        <p style="color:#3a3;">{$success}</p>
    {/if}

    <form method="post" action="{link application='wcf' controller='BanEdit' id=$ban.id}{/link}">
        <div class="formElement">
            <label>Spielername</label>
            <input type="text" name="player_name" value="{$ban.player_name}" required>
        </div>

        <div class="formElement">
            <label>SteamID</label>
            <input type="text" name="player_steamid" value="{$ban.player_steamid}" required>
        </div>

        <div class="formElement">
            <label>Grund</label>
            <input type="text" name="reason" value="{$ban.reason}" required>
        </div>

        <div class="formElement">
            <label>Dauer</label>
            <input type="text" name="duration" value="{$ban.duration}">
        </div>

        <button type="submit" class="button buttonPrimary">
            {lang}wcf.cs2bannliste.save{/lang}
        </button>
        <a href="{link application='wcf' controller='Banlist'}{/link}" class="button">
            {lang}wcf.global.back{/lang}
        </a>
    </form>
</section>

{include file='footer'}

