(function () {
  const STORAGE_KEY = 'stc_lang_v1';

  const translations = {
    en: {
      'site.name': 'Save The Chewbies',
      'common.skipToContent': 'Skip to content',
      'common.langToggle': '中文',
      'common.langToggleLabel': 'Switch language',

      'nav.home': 'Home',
      'nav.story': 'The True Story',
      'nav.gallery': 'Gallery',
      'nav.donate': 'Donate',
      'nav.privacy': 'Privacy',
      'nav.contact': 'Contact',
      'nav.ack': 'Acknowledgements',

      'footer.privacy': 'Privacy',
      'footer.contact': 'Contact',
      'footer.ack': 'Acknowledgements',

      'cookie.ariaLabel': 'Cookie and data notice',
      'cookie.textHtml':
        'We don’t use tracking cookies. Donations happen on Tiltify. We only store this preference in your browser. See <a class="text-link" href="./privacy.html">Privacy</a>.',
      'cookie.ok': 'OK',

      'img.proof.game1Alt': 'Tournament proof screenshot: Game 1',
      'img.proof.game10Alt': 'Tournament proof screenshot: Game 10',

      'index.meta.title': 'Save The Chewbies',
      'index.meta.description':
        'Donate to Save the Children via Tiltify, with donation incentives that update live.',
      'index.brandTagline': 'Donate to Save the Children',
      'index.hero.title': 'Make your donation count.',
      'index.hero.leadHtml':
        'This site is a simple hub to help you donate to <strong>Save the Children</strong> through <strong>Tiltify</strong>.',
      'index.hero.donateNow': 'Donate now',
      'index.hero.seeMilestones': 'See milestones',
      'index.howItWorks': 'How it works',
      'index.howItWorks.1Html': 'Click <strong>Donate now</strong>.',
      'index.howItWorks.2Html': 'Donate on Tiltify to <strong>Save the Children</strong>.',
      'index.howItWorks.3': 'Track progress on the milestones below.',
      'index.howItWorks.note': 'This site does not handle payment information.',
      'index.card.liveTotal': 'Live total',
      'index.card.nextLoading': 'Loading next milestone…',
      'index.card.donate': 'Donate',
      'index.card.saveTheChildren': 'Save the Children',
      'index.card.saveTheChildren.body':
        'A well-known charity with a long track record. This drive is focused on Save the Children.',
      'index.card.saveTheChildren.link': 'Donate via Tiltify →',
      'index.card.milestones': 'Milestones & Incentives',
      'index.card.milestones.body':
        'Track donation milestones to unlock special Baby Chew features and cosmetics in-game.',
      'index.card.milestones.link': 'View milestones →',
      'index.card.incentives': 'Incentives',
      'index.card.incentives.body':
        'We’re building five donation incentives tied to the Baby Chew in-game pet. They’ll be shown on Tiltify and mirrored here.',
      'index.card.incentives.link': 'See milestones →',
      'index.card.incentives.note':
        'I’ll also stream the implementation (Discord, and bilibili if possible). Stream times will be posted here.',
      'index.card.why351': 'Why €351?',
      'index.card.why351.body':
        'The incentive amounts are based on a Kerrigan Survival tournament on January 10–11, where players spawned a total of 351 baby chewbies.',
      'index.card.proof.game1': 'Proof screenshot (Game 1)',
      'index.card.proof.game10': 'Proof screenshot (Game 10)',
      'index.card.recentDonations': 'Recent Donations',
      'index.donations.loading': 'Loading donations...',

      'index.total.raised': '{total} raised',
      'index.total.raisedOf': '{total} raised of {goal}',
      'index.total.allUnlocked': 'All milestones unlocked. Thank you!',
      'index.total.next': '{remaining} to next milestone: {target} — {name}',
      'index.total.unavailable': 'Total unavailable',
      'index.total.enableHint':
        'Set tiltify_campaign_id (or milestone_total_override) to enable live totals.',
      'index.total.loadError': 'Could not load current total.',

      'donations.none': 'No donations yet.',
      'donations.noneFirst': 'No donations yet. Be the first!',
      'donations.noneDisplay': 'No donations to display.',
      'donations.loadError': 'Could not load donations.',
      'donations.anonymous': 'Anonymous',
      'donations.justNow': 'just now',
      'donations.minsAgo': '{m}m ago',
      'donations.hoursAgo': '{h}h ago',
      'donations.daysAgo': '{d}d ago',

      'milestone.1': 'Tantrum',
      'milestone.2': 'Playful sparring',
      'milestone.3': 'Ice caves',
      'milestone.4': 'Nydus teleport',
      'milestone.5': 'Mecha Baby Chew',

      'donate.meta.title': 'Donate • Save The Chewbies',
      'donate.meta.description':
        'Donate to Save the Children via Tiltify and support the Baby Chew fundraiser.',
      'donate.brandTagline': 'Donate',
      'donate.title': 'Donate',
      'donate.lead': 'Help Save the Children and unlock in-game rewards!',
      'donate.section.title': 'Donate to Save the Children',
      'donate.section.body':
        "Donations go directly through Tiltify's secure platform. Your contribution helps Save the Children and unlocks milestones below.",
      'donate.section.cta': 'Donate on Tiltify',
      'donate.section.note':
        'This site does not handle payment information. Donations are processed securely by Tiltify.',
      'donate.incentives.title': 'Incentives (milestones)',
      'donate.incentives.body':
        'To give donors extra motivation, we’re building five incentives tied to the Baby Chew in-game pet. Once the incentive list is finalized on Tiltify, it will be mirrored here.',
      'donate.incentives.loading': 'Milestones unlock in order. Loading current total…',
      'donate.incentives.note1': 'All milestones are listed here with their full descriptions.',
      'donate.incentives.note2':
        'I’ll be streaming the implementation of these features (Discord, and bilibili if possible). Exact stream times will be posted on the site.',
      'donate.incentives.note3':
        'Why €351? It comes from a Kerrigan Survival tournament on January 10–11 where players spawned a total of 351 baby chewbies.',
      'donate.incentives.creatorPledge':
        "Creator pledge (proof of concept): I'll personally donate €10 for every Baby Chew used in the tournament (6 total → €60) to help kick things off.",
      'donate.incentives.statusRaisedOf': 'Raised: {total} of {goal}',
      'donate.incentives.statusCurrentTotal': 'Current total: {total}',
      'donate.incentives.totalUnavailable':
        'Milestone total unavailable right now. Showing full list.',
      'donate.privacy.title': 'Privacy & contact',
      'donate.privacy.bodyHtml':
        'See <a href="./privacy.html">Privacy</a> for data-retention notes and <a href="./privacy.html#contact">Contact</a>.',

      'donate.m1.name': 'Tantrum (knockback reaction)',
      'donate.m1.desc':
        'When Baby Chew gets knocked back, the baby chewbies throw a little tantrum (harmless / cosmetic).',
      'donate.m1.liHtml':
        '<strong>€351 — Tantrum (knockback reaction):</strong> When Baby Chew gets knocked back, the baby chewbies throw a little tantrum (harmless / cosmetic).',
      'donate.m2.name': 'Playful sparring',
      'donate.m2.desc':
        'Baby chewbies can have tiny harmless fights with other players’ baby chewbies.',
      'donate.m2.liHtml':
        '<strong>€702 — Playful sparring:</strong> Baby chewbies can have tiny harmless fights with other players’ baby chewbies.',
      'donate.m3.name': 'Ice caves (build process)',
      'donate.m3.desc':
        'Baby Chew occasionally builds ice caves with a fun build animation.',
      'donate.m3.liHtml':
        '<strong>€702 — Ice caves (build process):</strong> Baby Chew occasionally builds ice caves with a fun build animation.',
      'donate.m4.name': 'Nydus teleport (fashionable)',
      'donate.m4.desc':
        'Baby Chew and the baby chewbies teleport to the current hero using the Nydus.',
      'donate.m4.liHtml':
        '<strong>€1053 — Nydus teleport (fashionable):</strong> Baby Chew and the baby chewbies teleport to the current hero using the Nydus.',
      'donate.m5.name': 'Mecha Baby Chew (skin)',
      'donate.m5.desc': 'A Mecha Baby Chew skin (like Mecha Chew, but for Baby Chew).',
      'donate.m5.liHtml':
        '<strong>€1404 — Mecha Baby Chew (skin):</strong> A Mecha Baby Chew skin (like Mecha Chew, but for Baby Chew).',

      'privacy.meta.title': 'Privacy & Contact • Save The Chewbies',
      'privacy.meta.description': 'Privacy notes and contact information for Save The Chewbies.',
      'privacy.brandTagline': 'Privacy & contact',
      'privacy.title': 'Privacy & contact',
      'privacy.lead': 'This is an early version. These notes will be refined as the project grows.',
      'privacy.section.title': 'Privacy',
      'privacy.section.body': 'This site is a simple fundraising hub. We keep data collection minimal.',
      'privacy.section.whatStore': 'What we store',
      'privacy.section.cookiesHtml':
        '<strong>Cookies & tracking:</strong> No tracking cookies or ad pixels are used. The cookie banner only stores your dismissal preference in your browser (via local storage).',
      'privacy.section.donationsHtml':
        '<strong>Donation data:</strong> We may receive webhook notifications from Tiltify about donations to display the total raised and recent donors.',
      'privacy.section.note':
        "We don't handle payment information. Donations happen on Tiltify's secure platform.",
      'privacy.section.retention': 'Retention',
      'privacy.section.retentionBody':
        'Donation totals and recent donor information are kept to display on the site. Personal data is minimal.',
      'privacy.contact.title': 'Contact',
      'privacy.contact.emailLabel': 'Email:',

      'gallery.meta.title': 'Gallery • Save The Chewbies',
      'gallery.meta.description': 'Community gallery of Baby Chew interactions and gameplay.',
      'gallery.brandTagline': 'Gallery',
      'gallery.title': 'Gallery',
      'gallery.lead':
        'Community screenshots and GIFs showcasing Baby Chew and the Chewbies in action.',
      'gallery.submit.title': 'Submit your screenshots',
      'gallery.submit.body':
        "Love Baby Chew? Have an amazing screenshot or GIF to share? Send them to us and we'll add them to the gallery!",
      'gallery.submit.emailHtml':
        '<strong>Email:</strong> <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px;">savethechewbies@protonmail.com</code>',
      'gallery.submit.include': 'Please include:',
      'gallery.submit.li1': 'Your screenshot or GIF (screenshots or GIFs are both welcome!)',
      'gallery.submit.li2': 'Your in-game name (optional, for credit)',
      'gallery.submit.li3': "A brief description of what's happening (optional)",
      'gallery.submit.note':
        "We'll review submissions and add the best ones to showcase the amazing interactions and moments with Baby Chew!",
      'gallery.coming.title': 'Gallery coming soon',
      'gallery.coming.body':
        "The gallery is currently empty, but with your help we'll fill it with amazing moments! Be the first to submit your screenshots and GIFs.",
      'gallery.links.privacy': 'Privacy →',
      'gallery.links.contact': 'Contact →',
      'gallery.links.ack': 'Acknowledgements →',

      'story.meta.title': 'The True Story • Save The Chewbies',
      'story.meta.description': 'The backstory of Baby Chew and the Chewbies.',
      'story.brandTagline': 'The True Story',
      'story.title': 'The True Story',
      'story.lead': 'How Baby Chew and the Chewbies came to be.',
      'story.h2.1': 'Chew what?',
      'story.p.1':
        'Chew and his chewbies are part of a StarCraft II Arcade game, developed by a bunch of passionate gamers. The name of the arcade map is Kerrigan Survival, which was originally released sometime in 201X (TBD). Since then, people from all over the world have spent countless hours improving it, slowly turning it into the game it is today.',
      'story.p.2':
        'StarCraft II is a real-time strategy game by Blizzard Entertainment, famous for its competitive multiplayer and its story-driven campaigns.',
      'story.h2.2': 'The beginning',
      'story.p.3':
        "Chew's story started with a silly idea — not even a real concept. Inspired by our long-term Kerrigan Survival moderator Chewbacca, Chew began as a very simple in-game pet: a snow matriarch from a distant, icy planet in the StarCraft II universe. Initially, Chew was meant as a heartfelt reward for Chewbacca's long-term support and dedication to Kerrigan Survival, and it was only usable by him.",
      'story.p.4':
        'But when I looked at all the animations the unnamed artists (and if you know — or are — the person who created the Snow Matriarch model and animations, PLEASE let me know, because whoever you are deserves some serious credit!) had put into this model, I saw the potential for something more.',
      'story.p.5':
        'What started as a meme class, spawning tiny, adorable chewbies, gradually became an actual support class. I added more and more abilities, but always stayed true to the core concept: one Papa Chew and lots of baby chewbies as his "ehm"… slightly disposable family.',
      'story.h2.3': 'The break',
      'story.p.6':
        "At one point, Chew became really strong — so strong that many players considered him overpowered. Sadly, during this period I was too busy with real-life stuff, so with a heavy heart I disabled Chew temporarily. For a few months now, players could no longer play him at all, until I would eventually find the time to make the class great again.",
      'story.p.7':
        "A couple of months later, I felt it was finally time for Chew's long-anticipated return. Some players were already getting a bit anxious, and I honestly felt guilty for making them wait so long. So I started a very openly communicated rework of the class, giving the community lots of insight and even asking for ideas to make Chew as good as possible.",
      'story.p.8':
        "I was on a good track — but then I got sidetracked by… something. I'm not even sure what anymore. I think I remembered my original Chew pet concept, and a somewhat twisted idea formed in my mind:",
      'story.p.9Html':
        '<em>"Let me just be a lil bit more me and create Baby Chew first."</em>',
      'story.h2.4': 'The "rework"',
      'story.p.10':
        'For a long time, many people (including devs) believed Kerrigan Survival to be a dying arcade in the slowly dwindling RTS genre. But as it turns out, our player base on the CN server is actually larger than EU and NA combined. That realization sparked a ton of motivation to create something I would personally consider amazing.',
      'story.p.11': 'So I made Baby Chew.',
      'story.p.12':
        "And no — I didn't just make a tiny thing that follows you around like the original pet. Quite the opposite. Baby Chew has tons of small interactions, hidden references, and jokes about long-forgotten abilities (RIP Feign Death). Baby Chew can be used by all 10 players simultaneously, creating a healthy amount of extra mayhem during matches.",
      'story.p.13':
        'At first, Baby Chew could only be summoned via a special chat command. Shortly after the later mentioned tournament, I added a proper button instead. Creating Baby Chew worked out so well that I genuinely felt it had the potential to become something even bigger.',
      'story.h2.5': 'The tournament',
      'story.p.14':
        'Purely by coincidence, shortly after my return to KS, I heard about a big tournament happening on the CN server on January 10th / 11th. I spent an hour or two polishing Baby Chew just in time so he could be revealed during the event.',
      'story.p.15': 'To further incentivize players, I offered to donate money to charity (Save the Children):',
      'story.p.16Html': '<strong>$1 per Baby Chew spawned during the tournament.</strong>',
      'story.p.17':
        "However, since the matches were high-level competitive play — and Baby Chew can actually be a bit of a disadvantage (he makes it easier to track your hero) — he didn't get much screen time. Only 6 Baby Chews were spawned in total (out of a possible 100).",
      'story.p.18': 'So I went with $10 per Baby Chew myself.',
      'story.h2.6': 'But what about the baby chewbies?',
      'story.p.19':
        "Those 6 Baby Chews managed to amass a total of 351 baby chewbies across just two games. And since I've clearly become somewhat of a Chew myself, I thought:",
      'story.p.20Html': '<em>"Somebody needs to take care of all these chewbies."</em>',
      'story.p.21': 'So I created the very website you are on right now.',
      'story.p.22': "To further incentivize donations for a good cause, I'm offering to continue improving Baby Chew for everyone!",
      'story.p.23':
        "(Yes, this currently means CN server only, because Baby Chew cannot be added to EU/NA right now. That's not on us — it's on Blizzard — but they promised to fix it Soon™)",
      'story.p.24': "By donating to help children around the world, you're also helping make Baby Chew even better.",
      'story.p.25Html': "And believe me when I tell you: <strong>he's already pretty fuckin' great.</strong>",
      'story.h2.7': 'note',
      'story.p.26': 'Baby Chew does not replace the original Chew.',
      'story.p.27': 'Chew will return to Kerrigan Survival (CN only for now) before the end of January 2026.',
      'story.p.28': 'Much love — and save the chewbies ❤️',
      'story.p.29': 'Sox',
      'story.ackLink': 'Acknowledgements →',

      'ack.meta.title': 'Acknowledgements • Save The Chewbies',
      'ack.meta.description': 'Acknowledgements for Save The Chewbies.',
      'ack.brandTagline': 'Acknowledgements',
      'ack.title': 'Acknowledgements',
      'ack.lead':
        'A huge thank-you to the people who helped (directly or indirectly) make this project possible.',
      'ack.ks.title': 'Kerrigan Survival (KS)',
      'ack.ks.li1Html': '<strong>Lumi</strong> — for keeping the KS dev team “chill and free”.',
      'ack.ks.li2Html': '<strong>Susu</strong> — for making sure it’s done right.',
      'ack.ks.li3Html': '<strong>Fighter</strong> — for being the UI beast he is (also: when Clara?).',
      'ack.ks.li4Html': '<strong>Templar</strong> — for making sure there will never be a hostile takeover on KS.',
      'ack.ks.li5Html': '<strong>Korneel</strong> — for always coming back.',
      'ack.ks.li6Html':
        '<strong>Zater</strong> — for helping me a ton with CN translation and handling our publish process on CN.',
      'ack.ks.li7Html': '<strong>Fern</strong> — for fighting for what is right.',
      'ack.ks.li8Html': '<strong>Willdroyd</strong> — for hyping me up (also: when Ele rework?).',
      'ack.ks.li9Html': '<strong>Chewy</strong> — for the name.',
      'ack.ks.li10':
        'And all the other great devs, mods, map makers, guide creators, and community contributors who have kept our project alive for all these years.',
      'ack.personal.title': 'Personal',
      'ack.personal.li1Html': '<strong>Michael</strong> — for helping me with the splash art.',
      'ack.personal.li2Html': '<strong>My SO</strong> — for her infinite patience.',
      'ack.personal.li3Html': '<strong>My friends</strong> — for hyping me up to do this project.',
      'ack.personal.li4Html':
        'And finally, <strong>my son</strong> — who inspired me to do all of this (and will hopefully cringe very hard when I tell him about it in a couple of years).',
      'ack.imgAlt': 'Acknowledgement image',

      'charities.meta.title': 'Charities • Save The Chewbies',
      'charities.meta.description': 'This project supports Save the Children.',
      'charities.title': 'Charity',
      'charities.leadHtml':
        'This project is currently focused on a single charity: <strong>Save the Children</strong>.',
      'charities.card.title': 'Save the Children',
      'charities.card.body':
        'A well-known charity with a long track record supporting children worldwide. This project uses Tiltify to make donating simple and track progress toward milestones.',
      'charities.card.focus': 'Focus',
      'charities.card.focusValue': 'Children',
      'charities.card.region': 'Region',
      'charities.card.regionValue': 'Global',
      'charities.card.cta1': 'Donate via Tiltify',
      'charities.card.cta2': 'Official site',
      'charities.how.title': 'How it works',
      'charities.how.body':
        "Donations go through Tiltify's secure platform and contribute to unlocking milestones. Track the total raised and see what incentives have been unlocked on the homepage.",
      'charities.how.note': 'Incentives are configured on Tiltify and displayed here.',

      'verify.meta.title': 'Verify donation • Save The Chewbies',
      'verify.meta.description': 'Verify your Tiltify donation with a code.',
      'verify.brandTagline': 'Donation verification',
      'verify.title': 'Verify your donation',
      'verify.lead':
        'If you started a verified donation flow, paste your verification code here.',
      'verify.form.label': 'Verification code',
      'verify.form.submit': 'Check',
      'verify.prefilled': 'We pre-filled your most recent verification code from this browser.',
      'verify.noCode.title': 'Don’t have a code?',
      'verify.noCode.body':
        'Start a verified donation from the Donate page (this uses platform webhooks instead of receipts).',
      'verify.notFound.title': 'Not found',
      'verify.notFound.body':
        'That code isn’t known on the server (yet). If you just donated, wait a minute and try again.',
      'verify.statusTitle': 'Status: {status}',
      'verify.statusLabel': 'Status:',
      'verify.status.charity': 'Charity:',
      'verify.status.started': 'Started:',
      'verify.status.verified': 'Verified:',
      'verify.how.title': 'How verification works',
      'verify.how.body':
        'This uses a donation platform’s webhook relay mechanism. That means the platform tells our server when your donation completes, and we mark the code as verified.',

      'admin.brandTagline': 'Admin',
      'admin.title': 'Admin • Save The Chewbies',
      'admin.meta.description': 'Admin dashboard for webhook relay and verification status.',
      'admin.header.title': 'Webhook admin',
      'admin.header.lead':
        'Shows relay statuses and the most recent webhook events seen by this server.',
    },
    zh: {
      'site.name': 'Save The Chewbies',
      'common.skipToContent': '跳到正文',
      'common.langToggle': 'English',
      'common.langToggleLabel': '切换语言',

      'nav.home': '主页',
      'nav.story': '真实故事',
      'nav.gallery': '图集',
      'nav.donate': '捐款',
      'nav.privacy': '隐私',
      'nav.contact': '联系',
      'nav.ack': '致谢',

      'footer.privacy': '隐私',
      'footer.contact': '联系',
      'footer.ack': '致谢',

      'cookie.ariaLabel': 'Cookie 与数据说明',
      'cookie.textHtml':
        '本站不使用追踪 Cookie。捐款在 Tiltify 上完成。我们只会在你的浏览器中保存这个提示的关闭偏好。详见 <a class="text-link" href="./privacy.html">隐私</a>。',
      'cookie.ok': '知道了',

      'img.proof.game1Alt': '锦标赛证明截图：第 1 局',
      'img.proof.game10Alt': '锦标赛证明截图：第 10 局',

      'index.meta.title': 'Save The Chewbies',
      'index.meta.description': '通过 Tiltify 为 Save the Children 捐款，并实时查看捐款里程碑与激励。',
      'index.brandTagline': '为 Save the Children 捐款',
      'index.hero.title': '让你的捐款更有意义。',
      'index.hero.leadHtml':
        '这里是一个简单的捐款中心，帮助你通过 <strong>Tiltify</strong> 为 <strong>Save the Children</strong> 捐款。',
      'index.hero.donateNow': '立即捐款',
      'index.hero.seeMilestones': '查看里程碑',
      'index.howItWorks': '如何参与',
      'index.howItWorks.1Html': '点击 <strong>立即捐款</strong>。',
      'index.howItWorks.2Html': '在 Tiltify 上为 <strong>Save the Children</strong> 捐款。',
      'index.howItWorks.3': '在下方查看里程碑进度。',
      'index.howItWorks.note': '本站不处理支付信息。',
      'index.card.liveTotal': '实时总额',
      'index.card.nextLoading': '正在加载下一个里程碑…',
      'index.card.donate': '捐款',
      'index.card.saveTheChildren': 'Save the Children',
      'index.card.saveTheChildren.body': '历史悠久、信誉良好的公益组织。本次活动仅支持 Save the Children。',
      'index.card.saveTheChildren.link': '通过 Tiltify 捐款 →',
      'index.card.milestones': '里程碑与激励',
      'index.card.milestones.body': '跟踪捐款里程碑，解锁游戏内 Baby Chew 的特殊功能与外观内容。',
      'index.card.milestones.link': '查看里程碑 →',
      'index.card.incentives': '激励内容',
      'index.card.incentives.body':
        '我们正在制作 5 个与游戏内宠物 Baby Chew 相关的捐款激励。它们会显示在 Tiltify 上，并同步到本站。',
      'index.card.incentives.link': '查看里程碑 →',
      'index.card.incentives.note':
        '我也会直播这些内容的实现过程（Discord，条件允许的话也会在 bilibili）。直播时间会发布在这里。',
      'index.card.why351': '为什么是 €351？',
      'index.card.why351.body':
        '激励金额来自 1 月 10–11 日的一场 Kerrigan Survival（KS）锦标赛：玩家一共召唤出了 351 只秋伊小跟班。',
      'index.card.proof.game1': '证明截图（第 1 局）',
      'index.card.proof.game10': '证明截图（第 10 局）',
      'index.card.recentDonations': '近期捐款',
      'index.donations.loading': '正在加载捐款…',

      'index.total.raised': '已筹集 {total}',
      'index.total.raisedOf': '已筹集 {total} / {goal}',
      'index.total.allUnlocked': '所有里程碑已解锁。感谢你的支持！',
      'index.total.next': '距离下一个里程碑还差 {remaining}：{target} — {name}',
      'index.total.unavailable': '暂时无法获取总额',
      'index.total.enableHint': '请设置 tiltify_campaign_id（或 milestone_total_override）以启用实时总额。',
      'index.total.loadError': '无法加载当前总额。',

      'donations.none': '还没有捐款记录。',
      'donations.noneFirst': '还没有捐款记录。来当第一个吧！',
      'donations.noneDisplay': '暂无可显示的捐款。',
      'donations.loadError': '无法加载捐款记录。',
      'donations.anonymous': '匿名',
      'donations.justNow': '刚刚',
      'donations.minsAgo': '{m} 分钟前',
      'donations.hoursAgo': '{h} 小时前',
      'donations.daysAgo': '{d} 天前',

      'milestone.1': '闹脾气',
      'milestone.2': '玩闹对练',
      'milestone.3': '冰洞',
      'milestone.4': '虫洞传送',
      'milestone.5': '机甲 Baby Chew',

      'donate.meta.title': '捐款 • Save The Chewbies',
      'donate.meta.description': '通过 Tiltify 为 Save the Children 捐款，并支持 Baby Chew 筹款活动。',
      'donate.brandTagline': '捐款',
      'donate.title': '捐款',
      'donate.lead': '为 Save the Children 出一份力，并解锁游戏内奖励！',
      'donate.section.title': '为 Save the Children 捐款',
      'donate.section.body': '捐款将通过 Tiltify 的安全平台直接完成。你的支持会帮助 Save the Children，并解锁下方里程碑。',
      'donate.section.cta': '前往 Tiltify 捐款',
      'donate.section.note': '本站不处理支付信息。捐款由 Tiltify 安全处理。',
      'donate.incentives.title': '激励（里程碑）',
      'donate.incentives.body':
        '为了给捐赠者更多动力，我们正在制作 5 个与游戏内宠物 Baby Chew 相关的激励内容。一旦 Tiltify 上的激励列表确定，将同步到这里。',
      'donate.incentives.loading': '里程碑将按顺序解锁。正在加载当前总额…',
      'donate.incentives.note1': '这里会列出所有里程碑及其完整说明。',
      'donate.incentives.note2':
        '我会直播这些功能的实现（Discord，条件允许的话也会在 bilibili）。具体时间会发布在网站上。',
      'donate.incentives.note3':
        '为什么是 €351？它来自 1 月 10–11 日的一场 Kerrigan Survival（KS）锦标赛：玩家一共召唤出了 351 只秋伊小跟班。',
      'donate.incentives.creatorPledge':
        '发起人承诺（概念验证）：我会为锦标赛中每一次使用 Baby Chew 额外捐出 €10（共 6 次 → €60），先把活动带起来。',
      'donate.incentives.statusRaisedOf': '已筹集：{total} / {goal}',
      'donate.incentives.statusCurrentTotal': '当前总额：{total}',
      'donate.incentives.totalUnavailable': '暂时无法获取里程碑总额。将显示完整列表。',
      'donate.privacy.title': '隐私与联系',
      'donate.privacy.bodyHtml': '数据保留说明见 <a href="./privacy.html">隐私</a>，联系方式见 <a href="./privacy.html#contact">联系</a>。',

      'donate.m1.name': '闹脾气（被击退反应）',
      'donate.m1.desc': '当 Baby Chew 被击退时，秋伊小跟班会闹点小脾气（无害 / 纯外观）。',
      'donate.m1.liHtml':
        '<strong>€351 — 闹脾气（被击退反应）：</strong>当 Baby Chew 被击退时，秋伊小跟班会闹点小脾气（无害 / 纯外观）。',
      'donate.m2.name': '玩闹对练',
      'donate.m2.desc': '秋伊小跟班可以和其他玩家的秋伊小跟班进行一些无害的小打闹。',
      'donate.m2.liHtml': '<strong>€702 — 玩闹对练：</strong>秋伊小跟班可以和其他玩家的秋伊小跟班进行一些无害的小打闹。',
      'donate.m3.name': '冰洞（建造过程）',
      'donate.m3.desc': 'Baby Chew 偶尔会建造冰洞，并带有有趣的建造动画。',
      'donate.m3.liHtml': '<strong>€702 — 冰洞（建造过程）：</strong>Baby Chew 偶尔会建造冰洞，并带有有趣的建造动画。',
      'donate.m4.name': '虫洞传送（更“时髦”的方式）',
      'donate.m4.desc': 'Baby Chew 与秋伊小跟班会使用虫洞传送到当前英雄身边。',
      'donate.m4.liHtml':
        '<strong>€1053 — 虫洞传送（更“时髦”的方式）：</strong>Baby Chew 与秋伊小跟班会使用虫洞传送到当前英雄身边。',
      'donate.m5.name': '机甲 Baby Chew（皮肤）',
      'donate.m5.desc': '为 Baby Chew 添加机甲皮肤（类似机甲秋伊，但目前 Baby Chew 还没有）。',
      'donate.m5.liHtml':
        '<strong>€1404 — 机甲 Baby Chew（皮肤）：</strong>为 Baby Chew 添加机甲皮肤（类似机甲秋伊，但目前 Baby Chew 还没有）。',

      'privacy.meta.title': '隐私与联系 • Save The Chewbies',
      'privacy.meta.description': 'Save The Chewbies 的隐私说明与联系方式。',
      'privacy.brandTagline': '隐私与联系',
      'privacy.title': '隐私与联系',
      'privacy.lead': '当前为早期版本。这些说明会随着项目发展逐步完善。',
      'privacy.section.title': '隐私',
      'privacy.section.body': '本站是一个简单的筹款中心，我们尽量减少数据收集。',
      'privacy.section.whatStore': '我们会保存什么',
      'privacy.section.cookiesHtml':
        '<strong>Cookie 与追踪：</strong>本站不使用追踪 Cookie 或广告像素。Cookie 提示仅会在你的浏览器中保存关闭偏好（通过本地存储）。',
      'privacy.section.donationsHtml':
        '<strong>捐款数据：</strong>我们可能会从 Tiltify 接收捐款的 webhook 通知，用于展示筹款总额与近期捐赠者。',
      'privacy.section.note': '本站不处理支付信息。捐款通过 Tiltify 的安全平台完成。',
      'privacy.section.retention': '保留周期',
      'privacy.section.retentionBody': '我们会保留用于展示的网站总额与近期捐赠者信息。个人数据尽量少。',
      'privacy.contact.title': '联系',
      'privacy.contact.emailLabel': '邮箱：',

      'gallery.meta.title': '图集 • Save The Chewbies',
      'gallery.meta.description': 'Baby Chew 互动与游戏内容的社区图集。',
      'gallery.brandTagline': '图集',
      'gallery.title': '图集',
      'gallery.lead': '社区截图与 GIF，展示 Baby Chew 与秋伊小跟班的精彩瞬间。',
      'gallery.submit.title': '投稿你的截图',
      'gallery.submit.body': '喜欢 Baby Chew？有精彩的截图或 GIF 想分享？发给我们，我们会添加到图集中！',
      'gallery.submit.emailHtml':
        '<strong>邮箱：</strong> <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px;">savethechewbies@protonmail.com</code>',
      'gallery.submit.include': '请包含：',
      'gallery.submit.li1': '你的截图或 GIF（两者都欢迎！）',
      'gallery.submit.li2': '你的游戏内昵称（可选，用于署名）',
      'gallery.submit.li3': '一句简短说明（可选）',
      'gallery.submit.note': '我们会审核投稿并挑选最精彩的内容，展示 Baby Chew 的有趣互动与瞬间！',
      'gallery.coming.title': '图集即将上线',
      'gallery.coming.body': '目前图集还是空的，但有你的帮助很快就能填满！快来成为第一个投稿的人吧。',
      'gallery.links.privacy': '隐私 →',
      'gallery.links.contact': '联系 →',
      'gallery.links.ack': '致谢 →',

      'story.meta.title': '真实故事 • Save The Chewbies',
      'story.meta.description': 'Baby Chew 与秋伊小跟班的来历。',
      'story.brandTagline': '真实故事',
      'story.title': '真实故事',
      'story.lead': 'Baby Chew 与秋伊小跟班是怎么诞生的。',
      'story.h2.1': '秋伊是什么？',
      'story.p.1':
        '秋伊（Chew）和他的秋伊小跟班（chewbies）来自《星际争霸 II》街机模式的一张地图，由一群热爱游戏的玩家共同开发。这张地图叫 Kerrigan Survival（KS），大约在 201X 年左右上线（待确认）。从那以后，来自世界各地的人投入了无数时间不断改进它，慢慢把它打磨成今天的样子。',
      'story.p.2':
        '《星际争霸 II》是暴雪娱乐出品的即时战略游戏，以高水平的多人对战与剧情战役而闻名。',
      'story.h2.2': '起点',
      'story.p.3':
        '秋伊的故事起源于一个很傻的想法——甚至算不上概念。受 KS 长期版主 Chewbacca 的启发，秋伊最初只是一个很简单的游戏内宠物：来自《星际争霸 II》宇宙中一颗遥远寒冷星球的“雪地族母”。一开始，秋伊是对 Chewbacca 长期支持与付出的真挚回馈，也只允许他一人使用。',
      'story.p.4':
        '但当我看到那些匿名作者为这个模型做出的丰富动画（如果你知道——或你就是——制作 Snow Matriarch 模型与动画的人，请务必联系我，因为你真的应该被狠狠表扬！）时，我意识到它还有更大的潜力。',
      'story.p.5':
        '最初只是个梗的职业：召唤一堆小小的、可爱的秋伊小跟班，逐渐变成了真正的辅助职业。我不断添加能力，但始终坚持核心设定：一个“秋伊爸爸”和一大群秋伊小跟班组成的——嗯——稍微有点“消耗品”味道的家庭。',
      'story.h2.3': '中断',
      'story.p.6':
        '有段时间，秋伊变得非常强——强到很多玩家觉得他过强。遗憾的是，当时我现实里太忙，只能忍痛暂时禁用秋伊。之后好几个月，玩家都无法再玩到他，直到我终于有时间把这个职业重新做得更好。',
      'story.p.7':
        '几个月后，我觉得是时候让大家期待已久的秋伊回归了。有些玩家已经开始焦虑，而我也确实因为让大家等太久而内疚。于是我开启了一次非常公开透明的重做，让社区看到很多进展，甚至征集大家的想法，希望把秋伊打磨到最好。',
      'story.p.8':
        '我一度进展顺利——但后来又被……某件事带偏了。我甚至不太记得是什么了。可能是我想起了最初的秋伊宠物概念，然后脑子里冒出了一个有点“扭曲”的念头：',
      'story.p.9Html': '<em>“让我更像我一点，先把 Baby Chew 做出来吧。”</em>',
      'story.h2.4': '“重做”',
      'story.p.10':
        '很长一段时间，很多人（包括开发者）都觉得 KS 是个在逐渐衰落的 RTS 品类里慢慢走向死亡的街机地图。但事实证明，CN 服务器的玩家基数其实比 EU 和 NA 加起来还大。这个发现让我充满动力，想做出一个我自己都觉得很棒的东西。',
      'story.p.11': '于是我做了 Baby Chew。',
      'story.p.12':
        '而且不——我不是做了一个像原版宠物那样跟着你的小玩意。恰恰相反。Baby Chew 有大量小互动、隐藏彩蛋，以及对一些早已被遗忘的能力的玩笑（RIP 假死）。Baby Chew 可以被 10 位玩家同时使用，在对局里制造恰到好处的额外混乱。',
      'story.p.13':
        '最开始，Baby Chew 只能通过一个特殊聊天指令召唤。后来在锦标赛之后不久，我加了一个正式按钮。Baby Chew 的效果出奇地好，让我觉得它完全有潜力变成更大的东西。',
      'story.h2.5': '锦标赛',
      'story.p.14':
        '纯属巧合的是，在我回归 KS 不久后，我听说 CN 服务器在 1 月 10 / 11 日要举办一场大型锦标赛。我赶紧花了一两个小时把 Baby Chew 打磨了一下，确保能在活动期间把它公布出来。',
      'story.p.15': '为了进一步激励玩家，我提出为公益组织（Save the Children）捐款：',
      'story.p.16Html': '<strong>每召唤一次 Baby Chew，我捐 $1。</strong>',
      'story.p.17':
        '不过，由于比赛是高水平的竞技对抗——而 Baby Chew 其实在某种程度上会成为劣势（它更容易暴露你的英雄位置）——所以镜头并不多。最终只召唤了 6 次 Baby Chew（最多可能 100 次）。',
      'story.p.18': '所以我改成了每次 Baby Chew 自掏腰包捐 $10。',
      'story.h2.6': '那秋伊小跟班呢？',
      'story.p.19':
        '这 6 次 Baby Chew 在仅仅两局里累计产出了 351 只秋伊小跟班。既然我自己显然也越来越像秋伊了，我就想：',
      'story.p.20Html': '<em>“总得有人照顾这些秋伊小跟班。”</em>',
      'story.p.21': '于是我就做了你现在正在看的这个网站。',
      'story.p.22': '为了进一步推动大家为善事捐款，我也承诺会继续为所有玩家改进 Baby Chew！',
      'story.p.23':
        '（是的，目前仅限 CN 服务器，因为 Baby Chew 暂时无法加入 EU/NA。这不是我们的问题——是暴雪的问题——不过他们承诺 Soon™ 会修好）',
      'story.p.24': '当你捐款帮助世界各地的孩子时，你也在帮助 Baby Chew 变得更好。',
      'story.p.25Html': '相信我：<strong>他现在已经强得离谱了。</strong>',
      'story.h2.7': '备注',
      'story.p.26': 'Baby Chew 不会替代原版秋伊（Chew）。',
      'story.p.27': '秋伊会在 2026 年 1 月底前回归 KS（目前仅限 CN 服务器）。',
      'story.p.28': '爱你们——也请一起拯救秋伊小跟班 ❤️',
      'story.p.29': 'Sox',
      'story.ackLink': '致谢 →',

      'ack.meta.title': '致谢 • Save The Chewbies',
      'ack.meta.description': 'Save The Chewbies 致谢。',
      'ack.brandTagline': '致谢',
      'ack.title': '致谢',
      'ack.lead': '衷心感谢那些直接或间接帮助本项目的人。',
      'ack.ks.title': 'Kerrigan Survival（KS）',
      'ack.ks.li1Html': '<strong>Lumi</strong> — 感谢你让 KS 的开发团队一直保持“轻松自在”。',
      'ack.ks.li2Html': '<strong>Susu</strong> — 感谢你确保一切都做对。',
      'ack.ks.li3Html': '<strong>Fighter</strong> — 感谢你作为 UI 怪物般的实力（另外：Clara 什么时候？）。',
      'ack.ks.li4Html': '<strong>Templar</strong> — 感谢你确保 KS 永远不会被“恶意夺权”。',
      'ack.ks.li5Html': '<strong>Korneel</strong> — 感谢你总会回来。',
      'ack.ks.li6Html': '<strong>Zater</strong> — 感谢你在中文翻译和 CN 发布流程上给了我大量帮助。',
      'ack.ks.li7Html': '<strong>Fern</strong> — 感谢你为正确的事情而战。',
      'ack.ks.li8Html': '<strong>Willdroyd</strong> — 感谢你一直鼓励我（另外：Ele 重做什么时候？）。',
      'ack.ks.li9Html': '<strong>Chewy</strong> — 感谢你提供了名字灵感。',
      'ack.ks.li10':
        '以及所有其他优秀的开发者、版主、制图者、攻略作者与社区贡献者：感谢你们这些年来一直让我们的项目保持活力。',
      'ack.personal.title': '个人',
      'ack.personal.li1Html': '<strong>Michael</strong> — 感谢你帮我制作了启动图（splash art）。',
      'ack.personal.li2Html': '<strong>我的另一半</strong> — 感谢你无穷的耐心。',
      'ack.personal.li3Html': '<strong>我的朋友们</strong> — 感谢你们一直鼓励我做这个项目。',
      'ack.personal.li4Html':
        '最后，<strong>我的儿子</strong> — 你启发了我去做这一切（希望几年后我告诉你这些的时候，你会狠狠地社死）。',
      'ack.imgAlt': '致谢图片',

      'charities.meta.title': '公益组织 • Save The Chewbies',
      'charities.meta.description': '本项目支持 Save the Children。',
      'charities.title': '公益组织',
      'charities.leadHtml': '本项目目前仅支持一个公益组织：<strong>Save the Children</strong>。',
      'charities.card.title': 'Save the Children',
      'charities.card.body':
        '历史悠久、面向全球儿童的公益组织。本项目通过 Tiltify 让捐款更简单，并跟踪里程碑进度。',
      'charities.card.focus': '关注领域',
      'charities.card.focusValue': '儿童',
      'charities.card.region': '地区',
      'charities.card.regionValue': '全球',
      'charities.card.cta1': '通过 Tiltify 捐款',
      'charities.card.cta2': '官方网站',
      'charities.how.title': '如何运作',
      'charities.how.body':
        '捐款会通过 Tiltify 的安全平台完成，并用于解锁里程碑。你可以在主页查看筹款总额以及已解锁的激励内容。',
      'charities.how.note': '激励内容在 Tiltify 上配置，并展示在本站。',

      'verify.meta.title': '验证捐款 • Save The Chewbies',
      'verify.meta.description': '使用验证码验证你的 Tiltify 捐款。',
      'verify.brandTagline': '捐款验证',
      'verify.title': '验证你的捐款',
      'verify.lead': '如果你启动了“已验证捐款”流程，请在这里粘贴验证码。',
      'verify.form.label': '验证码',
      'verify.form.submit': '查询',
      'verify.prefilled': '我们已从本浏览器自动填入你最近使用的验证码。',
      'verify.noCode.title': '没有验证码？',
      'verify.noCode.body': '请从“捐款”页面发起已验证捐款（使用平台 webhook，而不是收据）。',
      'verify.notFound.title': '未找到',
      'verify.notFound.body': '服务器暂时还不知道这个验证码。如果你刚刚捐款，请稍等一分钟再试。',
      'verify.statusTitle': '状态：{status}',
      'verify.statusLabel': '状态：',
      'verify.status.charity': '公益组织：',
      'verify.status.started': '开始时间：',
      'verify.status.verified': '验证时间：',
      'verify.how.title': '验证如何工作',
      'verify.how.body':
        '这里使用捐款平台的 webhook 转发机制：当你的捐款完成后，平台会通知我们的服务器，然后我们会将该验证码标记为已验证。',

      'admin.brandTagline': '管理',
      'admin.title': '管理 • Save The Chewbies',
      'admin.meta.description': 'Webhook 转发与验证状态的管理面板。',
      'admin.header.title': 'Webhook 管理',
      'admin.header.lead': '展示转发状态以及服务器最近收到的 webhook 事件。',
    },
  };

  function normalizeLang(lang) {
    if (!lang || typeof lang !== 'string') return 'en';
    const l = lang.toLowerCase();
    if (l === 'zh' || l.startsWith('zh-') || l.startsWith('zh_')) return 'zh';
    return 'en';
  }

  function getStoredLang() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      return normalizeLang(stored || '');
    } catch (_) {
      return 'en';
    }
  }

  function detectBrowserLang() {
    try {
      const langs = Array.isArray(navigator.languages) ? navigator.languages : [];
      const primary = typeof navigator.language === 'string' ? navigator.language : '';
      for (const l of langs) {
        const normalized = normalizeLang(l);
        if (normalized === 'zh') return 'zh';
      }
      return normalizeLang(primary);
    } catch (_) {
      return 'en';
    }
  }

  function interpolate(template, vars) {
    if (!vars) return template;
    return template.replace(/\{(\w+)\}/g, (_, key) =>
      Object.prototype.hasOwnProperty.call(vars, key) ? String(vars[key]) : `{${key}}`
    );
  }

  let currentLang = 'en';

  function t(key, vars) {
    const dict = translations[currentLang] || translations.en;
    const fallback = translations.en;
    const raw =
      (dict && Object.prototype.hasOwnProperty.call(dict, key) && dict[key]) ||
      (fallback && Object.prototype.hasOwnProperty.call(fallback, key) && fallback[key]) ||
      '';
    if (typeof raw !== 'string' || raw === '') return key;
    return interpolate(raw, vars);
  }

  function getIntlLocale() {
    return currentLang === 'zh' ? 'zh-CN' : 'en-US';
  }

  function setDocumentLang() {
    document.documentElement.lang = getIntlLocale();
  }

  function applyTranslations(root) {
    const scope = root && root.querySelectorAll ? root : document;

    scope.querySelectorAll('[data-i18n]').forEach((el) => {
      const key = el.getAttribute('data-i18n');
      if (!key) return;
      el.textContent = t(key);
    });

    scope.querySelectorAll('[data-i18n-html]').forEach((el) => {
      const key = el.getAttribute('data-i18n-html');
      if (!key) return;
      el.innerHTML = t(key);
    });

    scope.querySelectorAll('*').forEach((el) => {
      const attrs = el.getAttributeNames();
      for (const name of attrs) {
        if (!name.startsWith('data-i18n-attr-')) continue;
        const realAttr = name.slice('data-i18n-attr-'.length);
        const key = el.getAttribute(name);
        if (!realAttr || !key) continue;
        el.setAttribute(realAttr, t(key));
      }
    });
  }

  function persistLang(lang) {
    try {
      localStorage.setItem(STORAGE_KEY, lang);
    } catch (_) {
      // Ignore.
    }
  }

  function setLang(lang, opts) {
    currentLang = normalizeLang(lang);
    if (opts && opts.persist === true) persistLang(currentLang);
    setDocumentLang();
    applyTranslations();
    updateLangToggle();
  }

  function getLang() {
    return currentLang;
  }

  function translateMilestoneName(name) {
    if (currentLang !== 'zh') return name;
    if (typeof name !== 'string' || name.trim() === '') return name;
    const n = name.trim();
    const map = {
      Tantrum: t('milestone.1'),
      'Tantrum (knockback reaction)': t('donate.m1.name'),
      'Playful sparring': t('milestone.2'),
      'Ice caves': t('milestone.3'),
      'Ice caves (build process)': t('donate.m3.name'),
      'Nydus teleport': t('milestone.4'),
      'Nydus teleport (fashionable)': t('donate.m4.name'),
      'Mecha Baby Chew': t('milestone.5'),
      'Mecha Baby Chew (skin)': t('donate.m5.name'),
    };
    return map[n] || name;
  }

  function injectLangToggle() {
    const nav = document.querySelector('.site-header .nav');
    if (!nav) return;
    if (nav.querySelector('[data-stc-lang-toggle="1"]')) return;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'nav-link stc-lang-toggle';
    btn.setAttribute('data-stc-lang-toggle', '1');
    btn.setAttribute('aria-label', t('common.langToggleLabel'));
    btn.addEventListener('click', () => {
      const next = currentLang === 'zh' ? 'en' : 'zh';
      setLang(next, { persist: true });
      try {
        window.location.reload();
      } catch (_) {
        // Ignore.
      }
    });

    nav.appendChild(btn);
    updateLangToggle();
  }

  function updateLangToggle() {
    const btn = document.querySelector('[data-stc-lang-toggle="1"]');
    if (!btn) return;
    btn.textContent = t('common.langToggle');
    btn.setAttribute('aria-label', t('common.langToggleLabel'));
    btn.setAttribute('title', t('common.langToggleLabel'));
  }

  function init() {
    const stored = getStoredLang();
    const initial = stored !== 'en' || (function () {
      try {
        return localStorage.getItem(STORAGE_KEY) !== null;
      } catch (_) {
        return false;
      }
    })()
      ? stored
      : detectBrowserLang();

    currentLang = normalizeLang(initial);
    setDocumentLang();

    // Apply once immediately (works well when the script is loaded near the end of <body>),
    // then re-apply on DOMContentLoaded to catch any late-parsed nodes.
    applyTranslations();
    injectLangToggle();
    document.addEventListener(
      'DOMContentLoaded',
      () => {
        applyTranslations();
        injectLangToggle();
      },
      { once: true }
    );
  }

  window.STC_I18N = {
    t,
    getLang,
    setLang,
    getIntlLocale,
    applyTranslations,
    translateMilestoneName,
  };

  init();
})();
