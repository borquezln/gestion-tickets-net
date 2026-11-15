using System.Net;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.Mvc.Testing;

namespace GestionTickets.Api.Tests;

public sealed class StartupCorsConfigurationTests
{
    private const string ValidOrigin = "https://localhost:5173";

    [Fact]
    public void Startup_fails_when_cors_allowlist_is_empty()
    {
        using var factory = new StartupCorsFactory(environment: "Production");

        var exception = Assert.Throws<InvalidOperationException>(() => factory.CreateClient());

        Assert.Contains("Cors:AllowedOrigins", exception.Message, StringComparison.Ordinal);
    }

    [Fact]
    public void Startup_fails_when_cors_allowlist_is_absent()
    {
        var contentRoot = Directory.CreateTempSubdirectory("gestion-tickets-cors-");

        try
        {
            using var factory = new StartupCorsFactory(
                environment: "Production",
                contentRoot: contentRoot.FullName);

            var exception = Assert.Throws<InvalidOperationException>(() => factory.CreateClient());

            Assert.Contains("Cors:AllowedOrigins", exception.Message, StringComparison.Ordinal);
        }
        finally
        {
            contentRoot.Delete(recursive: true);
        }
    }

    [Fact]
    public async Task Startup_succeeds_with_a_valid_https_origin()
    {
        using var factory = new StartupCorsFactory(
            environment: "Production",
            validOrigin: ValidOrigin);
        using var client = factory.CreateClient();

        var response = await client.GetAsync("/api/v1/does-not-exist");

        Assert.Equal(HttpStatusCode.NotFound, response.StatusCode);
    }

    private sealed class StartupCorsFactory : WebApplicationFactory<Program>
    {
        private readonly string _environment;
        private readonly string? _contentRoot;
        private readonly string? _validOrigin;

        public StartupCorsFactory(
            string environment,
            string? contentRoot = null,
            string? validOrigin = null)
        {
            _environment = environment;
            _contentRoot = contentRoot;
            _validOrigin = validOrigin;
        }

        protected override void ConfigureWebHost(IWebHostBuilder builder)
        {
            builder.UseEnvironment(_environment);

            if (_contentRoot is not null)
            {
                builder.UseContentRoot(_contentRoot);
            }

            if (_validOrigin is not null)
            {
                builder.UseSetting("Cors:AllowedOrigins:0", _validOrigin);
            }
        }
    }
}
