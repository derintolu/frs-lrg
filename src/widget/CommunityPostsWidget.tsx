import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../frontend/portal/components/ui/card';
import { MessageSquare, Calendar, User, ExternalLink } from 'lucide-react';

interface CommunityPost {
  id: number;
  title: string;
  excerpt: string;
  content: string;
  date: string;
  author: string;
  authorAvatar?: string;
  permalink: string;
  featuredImage?: string;
}

interface CommunityPostsWidgetProps {
  limit?: number;
  showHeader?: boolean;
  title?: string;
  restUrl?: string;
  layout?: 'list' | 'grid' | 'compact';
}

export function CommunityPostsWidget({
  limit = 5,
  showHeader = true,
  title = 'The Pulse',
  restUrl = '/wp-json/wp/v2',
  layout = 'list'
}: CommunityPostsWidgetProps) {
  const [posts, setPosts] = useState<CommunityPost[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchPosts = async () => {
      try {
        setIsLoading(true);
        const response = await fetch(
          `${restUrl}/community-post?per_page=${limit}&_embed=true`
        );

        if (!response.ok) {
          throw new Error('Failed to fetch posts');
        }

        const data = await response.json();

        const formattedPosts: CommunityPost[] = data.map((post: any) => ({
          id: post.id,
          title: post.title?.rendered || 'Untitled',
          excerpt: post.excerpt?.rendered?.replace(/<[^>]*>/g, '').trim() || '',
          content: post.content?.rendered || '',
          date: new Date(post.date).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
          }),
          author: post._embedded?.author?.[0]?.name || 'Admin',
          authorAvatar: post._embedded?.author?.[0]?.avatar_urls?.['48'] || '',
          permalink: post.link,
          featuredImage: post._embedded?.['wp:featuredmedia']?.[0]?.source_url || ''
        }));

        setPosts(formattedPosts);
        setError(null);
      } catch (err) {
        console.error('Error fetching community posts:', err);
        setError('Unable to load posts');
      } finally {
        setIsLoading(false);
      }
    };

    fetchPosts();
  }, [limit, restUrl]);

  if (isLoading) {
    return (
      <Card className="h-full">
        {showHeader && (
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MessageSquare className="h-5 w-5" />
              {title}
            </CardTitle>
          </CardHeader>
        )}
        <CardContent>
          <div className="space-y-4">
            {[...Array(3)].map((_, i) => (
              <div key={i} className="animate-pulse">
                <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    );
  }

  if (error) {
    return (
      <Card className="h-full">
        {showHeader && (
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MessageSquare className="h-5 w-5" />
              {title}
            </CardTitle>
          </CardHeader>
        )}
        <CardContent>
          <p className="text-sm text-muted-foreground">{error}</p>
        </CardContent>
      </Card>
    );
  }

  if (posts.length === 0) {
    return (
      <Card className="h-full">
        {showHeader && (
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MessageSquare className="h-5 w-5" />
              {title}
            </CardTitle>
          </CardHeader>
        )}
        <CardContent>
          <p className="text-sm text-muted-foreground">No posts yet.</p>
        </CardContent>
      </Card>
    );
  }

  if (layout === 'compact') {
    return (
      <Card className="h-full">
        {showHeader && (
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MessageSquare className="h-5 w-5" />
              {title}
            </CardTitle>
          </CardHeader>
        )}
        <CardContent>
          <ul className="space-y-3">
            {posts.map((post) => (
              <li key={post.id}>
                <a
                  href={post.permalink}
                  className="group block hover:bg-muted/50 rounded-lg p-2 -m-2 transition-colors"
                >
                  <p className="font-medium text-sm group-hover:text-primary transition-colors line-clamp-1">
                    {post.title}
                  </p>
                  <p className="text-xs text-muted-foreground mt-0.5">
                    {post.date}
                  </p>
                </a>
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>
    );
  }

  if (layout === 'grid') {
    return (
      <div>
        {showHeader && (
          <h2 className="text-xl font-semibold flex items-center gap-2 mb-4">
            <MessageSquare className="h-5 w-5" />
            {title}
          </h2>
        )}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {posts.map((post) => (
            <Card key={post.id} className="overflow-hidden hover:shadow-lg transition-shadow">
              {post.featuredImage && (
                <div className="aspect-video overflow-hidden">
                  <img
                    src={post.featuredImage}
                    alt={post.title}
                    className="w-full h-full object-cover"
                  />
                </div>
              )}
              <CardContent className="p-4">
                <a href={post.permalink} className="group">
                  <h3 className="font-semibold group-hover:text-primary transition-colors line-clamp-2">
                    {post.title}
                  </h3>
                </a>
                <p className="text-sm text-muted-foreground mt-2 line-clamp-2">
                  {post.excerpt}
                </p>
                <div className="flex items-center gap-2 mt-3 text-xs text-muted-foreground">
                  <Calendar className="h-3 w-3" />
                  {post.date}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  // Default: list layout
  return (
    <Card className="h-full">
      {showHeader && (
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <MessageSquare className="h-5 w-5" />
            {title}
          </CardTitle>
        </CardHeader>
      )}
      <CardContent>
        <div className="space-y-4">
          {posts.map((post) => (
            <a
              key={post.id}
              href={post.permalink}
              className="group block p-4 -mx-4 hover:bg-muted/50 rounded-lg transition-colors"
            >
              <div className="flex gap-4">
                {post.featuredImage && (
                  <div className="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden">
                    <img
                      src={post.featuredImage}
                      alt={post.title}
                      className="w-full h-full object-cover"
                    />
                  </div>
                )}
                <div className="flex-1 min-w-0">
                  <h3 className="font-semibold group-hover:text-primary transition-colors line-clamp-1">
                    {post.title}
                  </h3>
                  <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
                    {post.excerpt}
                  </p>
                  <div className="flex items-center gap-4 mt-2 text-xs text-muted-foreground">
                    <span className="flex items-center gap-1">
                      <Calendar className="h-3 w-3" />
                      {post.date}
                    </span>
                    <span className="flex items-center gap-1">
                      <User className="h-3 w-3" />
                      {post.author}
                    </span>
                  </div>
                </div>
                <ExternalLink className="h-4 w-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" />
              </div>
            </a>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
