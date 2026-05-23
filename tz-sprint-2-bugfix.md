# ТЗ багфикс — Спринт 1 хвосты

**Версия:** 1.0
**Дата:** 23 мая 2026
**Спринт:** 2 (закрытие хвостов Спринта 1)
**Адресат:** Claude Code agent в VS Code
**Тип:** правка данных массива + правка вывода tags + правка одной строки главной
**Оценка:** малое-среднее
**Файлы:** `taxonomy-pa_fabric_color.php`, `front-page.php`

---

## ⚠️ Протокол ВНИМАНИЕ

```bash
git add -A && git commit -m "snapshot before sprint-2-bugfix" && git tag pre-sprint-2-bugfix
```
Если структура файла не совпадёт с описанием — остановись, сообщи Борису.

---

## Контекст — что недоделано

При реализации Спринта 1 (Блок C + E3) выпали три вещи:
1. Массивы `scenarios` у всех 17 цветов остались по 2 элемента (нужно 3) — поэтому блок «Рекомендуемые сценарии» показывает 2 карточки.
2. Массивы `tags` у всех 17 цветов остались старыми — с описательными словами («Свечи», «Минимализм», «Универсальный»), которые ведут в никуда.
3. Tags выводятся как нативный текст, а не как ссылки на сценарии.

Плюс отдельный мелкий баг: скатерть на главной запрашивает цвет, фото которого нет.

---

## ПРАВКА 1 — `scenarios` 2→3 для всех 17 цветов

**Файл:** `taxonomy-pa_fabric_color.php`, массив `$colors_data` (строки 14-234).

Для каждого цвета заменить строку `'scenarios' => [...]`. Ниже — точные новые значения (3 slug на цвет). **Найди строку у каждого цвета и замени массив scenarios.**

| Цвет (строка начала) | Было `scenarios` | Стало `scenarios` |
|---|---|---|
| fioletovyj (~14) | `['romanticheskij-uzhin', 'prazdnichnyj-stol']` | `['romanticheskij-uzhin', 'prazdnichnyj-stol', 'den-rozhdenija']` |
| grafit (~27) | `['romanticheskij-uzhin', 'kazhdyj-den']` | `['romanticheskij-uzhin', 'kazhdyj-den', 'prazdnichnyj-stol']` |
| bronza (~40) | `['prazdnichnyj-stol', 'romanticheskij-uzhin']` | `['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija']` |
| sirenevyj (~53) | `['romanticheskij-uzhin', 'semejnyj-obed']` | `['romanticheskij-uzhin', 'den-rozhdenija', 'semejnyj-obed']` |
| bezhevyj (~66) | `['kazhdyj-den', 'semejnyj-obed']` | `['semejnyj-obed', 'kazhdyj-den', 'prazdnichnyj-stol']` |
| belyj (~79) | `['prazdnichnyj-stol', 'semejnyj-obed']` | `['prazdnichnyj-stol', 'romanticheskij-uzhin', 'kazhdyj-den']` |
| biryuza (~92) | `['semejnyj-obed', 'kazhdyj-den']` | `['semejnyj-obed', 'den-rozhdenija', 'kazhdyj-den']` |
| blek-zoloto (~105) | `['romanticheskij-uzhin', 'prazdnichnyj-stol']` | `['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija']` |
| goluboj (~118) | `['semejnyj-obed', 'kazhdyj-den']` | `['semejnyj-obed', 'kazhdyj-den', 'den-rozhdenija']` |
| zelenyj (~131) | `['semejnyj-obed', 'kazhdyj-den']` | `['semejnyj-obed', 'kazhdyj-den', 'prazdnichnyj-stol']` |
| melanzh-zoloto (~144) | `['prazdnichnyj-stol', 'romanticheskij-uzhin']` | `['prazdnichnyj-stol', 'den-rozhdenija', 'romanticheskij-uzhin']` |
| melanzh-serebro (~157) | `['kazhdyj-den', 'semejnyj-obed']` | `['prazdnichnyj-stol', 'kazhdyj-den', 'semejnyj-obed']` |
| melanzh-seryj (~170) | `['kazhdyj-den', 'semejnyj-obed']` | `['kazhdyj-den', 'semejnyj-obed', 'romanticheskij-uzhin']` |
| melanzh-chernyj (~183) | `['kazhdyj-den', 'romanticheskij-uzhin']` | `['romanticheskij-uzhin', 'prazdnichnyj-stol', 'den-rozhdenija']` |
| platina (~196) | `['kazhdyj-den', 'semejnyj-obed']` | `['prazdnichnyj-stol', 'kazhdyj-den', 'romanticheskij-uzhin']` |
| serebro (~209) | `['prazdnichnyj-stol', 'semejnyj-obed']` | `['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija']` |
| temno-biryuzovyj (~222) | `['prazdnichnyj-stol', 'romanticheskij-uzhin']` | `['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija']` |

---

## ПРАВКА 2 — `tags` для всех 17 цветов (= названия сценариев)

**Файл:** тот же, те же блоки. Заменить `'tags' => [...]`. Новые tags должны **совпадать по смыслу с scenarios** (по порядку), чтобы в Правке 3 стать кликабельными ссылками на эти сценарии.

| Цвет | Стало `tags` |
|---|---|
| fioletovyj | `['Романтический ужин', 'Праздничный стол', 'День рождения']` |
| grafit | `['Романтический ужин', 'Каждый день', 'Праздничный стол']` |
| bronza | `['Праздничный стол', 'Романтический ужин', 'День рождения']` |
| sirenevyj | `['Романтический ужин', 'День рождения', 'Семейный обед']` |
| bezhevyj | `['Семейный обед', 'Каждый день', 'Праздничный стол']` |
| belyj | `['Праздничный стол', 'Романтический ужин', 'Каждый день']` |
| biryuza | `['Семейный обед', 'День рождения', 'Каждый день']` |
| blek-zoloto | `['Праздничный стол', 'Романтический ужин', 'День рождения']` |
| goluboj | `['Семейный обед', 'Каждый день', 'День рождения']` |
| zelenyj | `['Семейный обед', 'Каждый день', 'Праздничный стол']` |
| melanzh-zoloto | `['Праздничный стол', 'День рождения', 'Романтический ужин']` |
| melanzh-serebro | `['Праздничный стол', 'Каждый день', 'Семейный обед']` |
| melanzh-seryj | `['Каждый день', 'Семейный обед', 'Романтический ужин']` |
| melanzh-chernyj | `['Романтический ужин', 'Праздничный стол', 'День рождения']` |
| platina | `['Праздничный стол', 'Каждый день', 'Романтический ужин']` |
| serebro | `['Праздничный стол', 'Романтический ужин', 'День рождения']` |
| temno-biryuzovyj | `['Праздничный стол', 'Романтический ужин', 'День рождения']` |

**Важно:** порядок tags синхронизирован с порядком scenarios (Правка 1) — tag[0] соответствует scenarios[0] и т.д. Это нужно для Правки 3.

---

## ПРАВКА 3 — сделать tags кликабельными

**Файл:** `taxonomy-pa_fabric_color.php`, hero-блок, вывод tags.

**Найди** текущий вывод tags (ищи класс `chc-tag`). Скорее всего так:
```php
<?php foreach ($color['tags'] as $tag) : ?>
    <span class="chc-tag"><?php echo esc_html($tag); ?></span>
<?php endforeach; ?>
```

**Замени на** (tag по индексу получает ссылку на соответствующий scenario):
```php
<?php
$tag_scenarios = isset($color['scenarios']) ? $color['scenarios'] : [];
foreach ($color['tags'] as $i => $tag) :
    $sc_slug = isset($tag_scenarios[$i]) ? $tag_scenarios[$i] : '';
?>
    <?php if ($sc_slug) : ?>
        <a href="<?php echo home_url('/scenarios/' . $sc_slug . '/'); ?>" class="chc-tag"><?php echo esc_html($tag); ?></a>
    <?php else : ?>
        <span class="chc-tag"><?php echo esc_html($tag); ?></span>
    <?php endif; ?>
<?php endforeach; ?>
```

**CSS:** класс `.chc-tag` уже стилизован. Для `<a>` ничего добавлять не нужно (наследует). Если визуально тег-ссылка станет подчёркнутой синей — добавь в `style.css`:
```css
a.chc-tag { text-decoration: none; color: inherit; }
```

---

## ПРАВКА 4 — скатерть на главной (временная заплатка)

**Файл:** `front-page.php`, строка 141.

**Найди:**
```php
$pp_tablecloth_url = function_exists('loraleya_get_color_photo_url') ? loraleya_get_color_photo_url('platina', 'skatert')              : '';
```

**Замени `'platina'` на `'belyj'`:**
```php
$pp_tablecloth_url = function_exists('loraleya_get_color_photo_url') ? loraleya_get_color_photo_url('belyj', 'skatert')              : '';
```

**Причина:** фото `platina-skatert` нет в Media Library (подтверждено аудитом), а `beliy-skatert` есть. Это временно — когда Куренкова доснимет платиновую скатерть, можно вернуть `'platina'` обратно (или оставить `belyj`, на усмотрение Бориса).

---

## Проверка

- [ ] Открой `/color/bezhevyj/` → блок «Рекомендуемые сценарии» показывает **3** карточки
- [ ] Открой `/color/fioletovyj/` → 3 карточки, среди них «День рождения»
- [ ] Теги под описанием на любой цветовой странице — **кликабельны**, ведут на правильные сценарии (тег «Семейный обед» → `/scenarios/semejnyj-obed/`)
- [ ] Проверь 3-4 разных цвета — везде 3 карточки и кликабельные теги
- [ ] Открой главную → блок «Что входит в сервировку» → у Скатертей появилось фото (белая скатерть)
- [ ] Никакие другие блоки не сломались (hero, палитра, наборы, поштучно, FAQ)
- [ ] PHP без ошибок (открой любую цветовую страницу — не должно быть белого экрана)

### Откат при проблеме
```bash
git reset --hard pre-sprint-2-bugfix
```

---

## Что НЕ делаем

1. НЕ трогаем seo_title/seo_description/seo_text/seo_faq (залиты скриптом E4, корректны)
2. НЕ трогаем subtitle/desc цветов (применены в Спринте 1, корректны)
3. НЕ трогаем шаблон сценариев, главную (кроме строки 141), функции
4. НЕ загружаем фото

---

## Финал

```bash
git add wp-content/themes/loraleya/taxonomy-pa_fabric_color.php wp-content/themes/loraleya/front-page.php wp-content/themes/loraleya/style.css
git commit -m "sprint-2-bugfix: scenarios 2to3, clickable tags, main skatert fallback"
```

Сообщи Борису: подтверждение по чек-листу + скрин цветовой страницы (3 карточки + кликабельные теги) + скрин главной (скатерть с фото).

---

**Конец ТЗ.**
