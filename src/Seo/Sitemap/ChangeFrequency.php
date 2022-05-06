<?php

namespace AloiaCms\Seo\Sitemap;

enum ChangeFrequency: string
{
    case Daily = "daily";
    case Weekly = "weekly";
    case Monthly = "monthly";
    case Yearly = "yearly";
}