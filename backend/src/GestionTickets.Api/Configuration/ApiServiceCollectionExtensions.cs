namespace GestionTickets.Api.Configuration;

public static class ApiServiceCollectionExtensions
{
    /// <summary>
    /// Registers the API transport configuration required by the HTTP pipeline.
    /// </summary>
    public static IServiceCollection AddApiConfiguration(
        this IServiceCollection services,
        IConfiguration configuration)
    {
        ArgumentNullException.ThrowIfNull(services);
        ArgumentNullException.ThrowIfNull(configuration);

        var corsSettings = configuration
            .GetSection(CorsSettings.SectionName)
            .Get<CorsSettings>() ?? new CorsSettings();

        corsSettings.Validate();

        services.Configure<CorsSettings>(configuration.GetSection(CorsSettings.SectionName));
        services.AddCors(options =>
        {
            options.AddPolicy(CorsSettings.PolicyName, policy =>
            {
                policy
                    .WithOrigins(corsSettings.AllowedOrigins)
                    .AllowAnyHeader()
                    .AllowAnyMethod()
                    .AllowCredentials();
            });
        });

        return services;
    }
}
