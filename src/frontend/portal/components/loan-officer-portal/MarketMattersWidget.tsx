import { useState, useEffect } from 'react';
import { TrendingUp, TrendingDown, ExternalLink, TrendingUpIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';

interface MortgageRateData {
  frm_30: string;
  frm_15: string;
  week: string;
}

interface MortgageRate {
  week: string;
  data: MortgageRateData;
}

interface BlogPost {
  id: number;
  title: string;
  excerpt: string;
  link: string;
  date: string;
  featured_image?: string;
  author_name?: string;
  author_avatar?: string;
  category_name?: string;
}

export function MarketMattersWidget() {
  const [rates, setRates] = useState<MortgageRate | null>(null);
  const [blogPosts, setBlogPosts] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  // Fetch mortgage rates
  useEffect(() => {
    const fetchRates = async () => {
      try {
        const response = await fetch('https://api.api-ninjas.com/v1/mortgagerate', {
          method: 'GET',
          headers: {
            'X-Api-Key': 'TYgp30Q8LTuwp3KTbCku1Q==MFnAgH2amAue4QiZ',
          },
        });

        if (response.ok) {
          const data: MortgageRate[] = await response.json();
          if (data && data.length > 0) {
            setRates(data[0]);
          }
        }
      } catch (err) {
        console.error('Failed to fetch mortgage rates:', err);
        // Fallback rates
        setRates({
          week: 'current',
          data: {
            frm_30: '6.85',
            frm_15: '6.10',
            week: new Date().toISOString().split('T')[0],
          }
        });
      }
    };

    fetchRates();
  }, []);

  // Fetch blog posts
  useEffect(() => {
    const fetchBlogPosts = async () => {
      try {
        const response = await fetch('/wp-json/wp/v2/posts?per_page=2&_embed');
        if (response.ok) {
          const posts = await response.json();
          const formattedPosts = posts.map((post: any) => ({
            id: post.id,
            title: post.title.rendered,
            excerpt: post.excerpt.rendered.replace(/<[^>]*>/g, '').substring(0, 80),
            link: post.link,
            date: new Date(post.date).toLocaleDateString('en-US', {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
            }),
            featured_image: post._embedded?.['wp:featuredmedia']?.[0]?.source_url,
            author_name: post._embedded?.author?.[0]?.name || 'Author',
            author_avatar: post._embedded?.author?.[0]?.avatar_urls?.['96'] || '',
            category_name: post._embedded?.['wp:term']?.[0]?.[0]?.name || 'Blog',
          }));
          setBlogPosts(formattedPosts);
        }
      } catch (err) {
        console.error('Failed to fetch blog posts:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchBlogPosts();
  }, []);

  if (loading) {
    return (
      <>
        <CardHeader className="pt-4 px-4 md:px-6 pb-0 backdrop-blur-xl bg-gradient-to-br from-white/10 to-white/5 border-b border-white/20">
          <CardTitle className="flex items-center gap-2 text-base md:text-lg">
            <TrendingUp className="h-4 w-4 md:h-5 md:w-5 text-[#2dd4da]" />
            <span className="bg-gradient-to-r from-[#2563eb] to-[#2dd4da] bg-clip-text text-transparent">
            Market Matters
            </span>
          </CardTitle>
        </CardHeader>
        <CardContent className="p-4 md:p-6 pt-3">
          <div className="grid grid-cols-2 gap-3 animate-pulse">
            <div className="h-32 bg-white/10"></div>
            <div className="h-32 bg-white/10"></div>
            <div className="h-32 bg-white/10"></div>
            <div className="h-32 bg-white/10"></div>
          </div>
        </CardContent>
      </>
    );
  }

  return (
    <>
      <CardHeader className="pt-4 px-4 md:px-6 pb-0 backdrop-blur-xl bg-gradient-to-br from-white/10 to-white/5 border-b border-white/20">
        <CardTitle className="flex items-center gap-2 text-base md:text-lg">
          <TrendingUp className="h-4 w-4 md:h-5 md:w-5 text-[#2dd4da]" />
          <span className="bg-gradient-to-r from-[#2563eb] to-[#2dd4da] bg-clip-text text-transparent">
          Market Matters
          </span>
        </CardTitle>
      </CardHeader>
      <CardContent className="p-4 md:p-6 pt-3">
        <div className="grid grid-cols-2 gap-3">
          {/* 30-Year Rate Tile */}
          <div className="relative overflow-hidden p-4 backdrop-blur-xl bg-gradient-to-br from-white/15 to-white/5 border border-white/30 shadow-lg">
            <div className="relative z-10">
              <div className="flex items-center justify-between mb-1">
                <span className="text-xs font-medium text-white">30-Year Fixed</span>
                <TrendingUp className="h-3 w-3 text-white/80" />
              </div>
              <div className="text-2xl font-bold bg-gradient-to-r from-[#2563eb] to-[#2dd4da] bg-clip-text text-transparent">
                {rates?.data?.frm_30 ? parseFloat(rates.data.frm_30).toFixed(2) : '—'}%
              </div>
              <div className="text-[10px] text-white/70 mt-0.5">
                {rates?.data?.week ? new Date(rates.data.week).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : 'N/A'}
              </div>
            </div>
          </div>

          {/* 15-Year Rate Tile */}
          <div className="relative overflow-hidden p-4 backdrop-blur-xl bg-gradient-to-br from-white/15 to-white/5 border border-white/30 shadow-lg">
            <div className="relative z-10">
              <div className="flex items-center justify-between mb-1">
                <span className="text-xs font-medium text-white">15-Year Fixed</span>
                <TrendingDown className="h-3 w-3 text-white/80" />
              </div>
              <div className="text-2xl font-bold bg-gradient-to-r from-[#2563eb] to-[#2dd4da] bg-clip-text text-transparent">
                {rates?.data?.frm_15 ? parseFloat(rates.data.frm_15).toFixed(2) : '—'}%
              </div>
              <div className="text-[10px] text-white/70 mt-0.5">
                {rates?.data?.week ? new Date(rates.data.week).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : 'N/A'}
              </div>
            </div>
          </div>

          {/* Market Update 1 */}
          {blogPosts.length > 0 && (
            <a
              href={blogPosts[0].link}
              target="_blank"
              rel="noopener noreferrer"
              className="relative overflow-hidden p-3 group cursor-pointer no-underline flex flex-col justify-between backdrop-blur-xl bg-gradient-to-br from-white/15 to-white/5 border border-white/30 shadow-lg hover:from-white/20 hover:to-white/10 transition-all duration-300"
            >
              <div className="relative z-10">
                <div className="text-xs font-medium text-white mb-2 flex items-center justify-between">
                  <span>Market Update</span>
                  <ExternalLink className="h-3 w-3 text-white group-hover:text-white/90 transition-colors" />
                </div>
                <div className="text-sm font-bold bg-gradient-to-r from-[#2563eb] to-[#2dd4da] bg-clip-text text-transparent line-clamp-2 leading-tight mb-2">
                  {blogPosts[0].title}
                </div>
                <p className="text-xs text-white/90 line-clamp-2 mb-2">{blogPosts[0].excerpt}</p>
              </div>

              {/* Author & Meta */}
              <div className="flex items-center gap-2 mt-auto">
                <img
                  src={blogPosts[0].author_avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(blogPosts[0].author_name || 'Author')}&background=2DD4DA&color=fff&size=96`}
                  alt={blogPosts[0].author_name}
                  className="w-5 h-5 rounded-full border border-white/20"
                />
                <div className="flex flex-col flex-1 min-w-0">
                  <span className="text-[10px] text-white font-medium truncate">{blogPosts[0].author_name}</span>
                  <span className="text-[9px] text-white/80">{blogPosts[0].date}</span>
                </div>
              </div>
            </a>
          )}

          {/* Market Update 2 */}
          {blogPosts.length > 1 && (
            <a
              href={blogPosts[1].link}
              target="_blank"
              rel="noopener noreferrer"
              className="relative overflow-hidden p-3 group cursor-pointer no-underline flex flex-col justify-between backdrop-blur-xl bg-gradient-to-br from-white/15 to-white/5 border border-white/30 shadow-lg hover:from-white/20 hover:to-white/10 transition-all duration-300"
            >
              <div className="relative z-10">
                <div className="text-xs font-medium text-white mb-2 flex items-center justify-between">
                  <span>Market Update</span>
                  <ExternalLink className="h-3 w-3 text-white group-hover:text-white/90 transition-colors" />
                </div>
                <div className="text-sm font-bold bg-gradient-to-r from-[#2563eb] to-[#2dd4da] bg-clip-text text-transparent line-clamp-2 leading-tight mb-2">
                  {blogPosts[1].title}
                </div>
                <p className="text-xs text-white/90 line-clamp-2 mb-2">{blogPosts[1].excerpt}</p>
              </div>

              {/* Author & Meta */}
              <div className="flex items-center gap-2 mt-auto">
                <img
                  src={blogPosts[1].author_avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(blogPosts[1].author_name || 'Author')}&background=2DD4DA&color=fff&size=96`}
                  alt={blogPosts[1].author_name}
                  className="w-5 h-5 rounded-full border border-white/20"
                />
                <div className="flex flex-col flex-1 min-w-0">
                  <span className="text-[10px] text-white font-medium truncate">{blogPosts[1].author_name}</span>
                  <span className="text-[9px] text-white/80">{blogPosts[1].date}</span>
                </div>
              </div>
            </a>
          )}
        </div>
      </CardContent>
    </>
  );
}
