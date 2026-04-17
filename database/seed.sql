
INSERT INTO categories (name, slug) VALUES
('Tutorial', 'tutorial'),
('Development', 'development'),
('Security', 'security'),
('Design', 'design'),
('Technology', 'technology');

INSERT INTO tags (name, slug) VALUES
('Beginner', 'beginner'),
('PHP', 'php'),
('MySQL', 'mysql'),
('CMS', 'cms'),
('Web Design', 'web-design');

INSERT INTO posts (title, slug, content, category_id, featured_image, status, is_pinned, published_at) VALUES
('Getting Started with MiniPress', 'getting-started-with-minipress',
'This is the first sample post for MiniPress CMS. It introduces the basic setup and purpose of the system.',
1, NULL, 'published', 1, NOW()),

('Web Development Best Practices', 'web-development-best-practices',
'This sample article talks about clean structure, readable code, and proper project organization for web applications.',
2, NULL, 'published', 0, NOW()),

('How to Secure Your Website', 'how-to-secure-your-website',
'This post discusses authentication, password hashing, input validation, and session protection.',
3, NULL, 'draft', 0, NULL),

('Understanding UI Layout Design', 'understanding-ui-layout-design',
'This post explains spacing, alignment, cards, sidebars, typography, and modern CMS dashboard layout patterns.',
4, NULL, 'published', 0, NOW()),

('The Future of CMS Platforms', 'the-future-of-cms-platforms',
'This sample article explores how content management systems continue to evolve for blogs, business websites, and custom platforms.',
5, NULL, 'draft', 0, NULL);

INSERT INTO post_tags (post_id, tag_id) VALUES
(1, 1),
(1, 4),
(2, 2),
(2, 5),
(3, 3),
(3, 4),
(4, 5),
(5, 4);

INSERT INTO pages (title, slug, content, status) VALUES
('About Us', 'about-us',
'This is the About Us page of MiniPress CMS. It can contain company or project information.', 'published'),

('Contact', 'contact',
'This is the Contact page. It can contain contact details, form instructions, and social links.', 'published'),

('Privacy Policy', 'privacy-policy',
'This page contains the privacy and data usage policy for the website.', 'draft');