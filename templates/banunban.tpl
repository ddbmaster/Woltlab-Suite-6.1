{include file='header'}

<section class="section">
    <header class="sectionHeader">
        <h1>{lang}wcf.cs2bannliste.unban{/lang}</h1>
    </header>

    {if $error}
        <p style="color:#c00; font-weight:bold;">{$error}</p>
    {elseif $success}
        <p style="color:#3a3;">{$success}</p>
    {else}
        <p>Möchten Sie diesen Spieler wirklich entbannen?</p>
        <form method="post" action="{link application='wcf' controller='BanUnban' id=$ban.id}{/link}">
            <button type="submit" class="button buttonPrimary">
                {lang}wcf.cs2bannliste.confirmUnban{/lang}
            </button>
            <a href="{link application='wcf' controller='Banlist'}{/link}" class="button">
                {lang}wcf.global.cancel{/lang}
            </a>
        </form>
    {/if}
</section>

{include file='footer'}

